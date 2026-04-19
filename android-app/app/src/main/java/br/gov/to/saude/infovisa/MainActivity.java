package br.gov.to.saude.infovisa;

import android.Manifest;
import android.app.Activity;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.view.KeyEvent;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat;
import androidx.core.content.FileProvider;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;
import androidx.work.Constraints;
import androidx.work.ExistingPeriodicWorkPolicy;
import androidx.work.NetworkType;
import androidx.work.PeriodicWorkRequest;
import androidx.work.WorkManager;

import java.io.File;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;
import java.util.concurrent.TimeUnit;

public class MainActivity extends AppCompatActivity {

    private WebView webView;
    private SwipeRefreshLayout swipeRefresh;
    private ProgressBar progressBar;
    private LinearLayout errorLayout;
    private ValueCallback<Uri[]> fileUploadCallback;
    private Uri cameraPhotoUri;

    private final ActivityResultLauncher<Intent> fileChooserLauncher =
            registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
                if (fileUploadCallback == null) return;

                Uri[] results = null;
                if (result.getResultCode() == Activity.RESULT_OK && result.getData() != null) {
                    String dataString = result.getData().getDataString();
                    if (dataString != null) {
                        results = new Uri[]{Uri.parse(dataString)};
                    }
                } else if (result.getResultCode() == Activity.RESULT_OK && cameraPhotoUri != null) {
                    results = new Uri[]{cameraPhotoUri};
                }

                fileUploadCallback.onReceiveValue(results);
                fileUploadCallback = null;
            });

    private final ActivityResultLauncher<String> permissionLauncher =
            registerForActivityResult(new ActivityResultContracts.RequestPermission(), isGranted -> {
                // Permissão tratada
            });

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        webView = findViewById(R.id.webView);
        swipeRefresh = findViewById(R.id.swipeRefresh);
        progressBar = findViewById(R.id.progressBar);
        errorLayout = findViewById(R.id.errorLayout);

        setupWebView();
        setupSwipeRefresh();
        requestPermissions();

        // Botão tentar novamente
        TextView retryBtn = findViewById(R.id.btnRetry);
        if (retryBtn != null) {
            retryBtn.setOnClickListener(v -> {
                errorLayout.setVisibility(View.GONE);
                webView.setVisibility(View.VISIBLE);
                webView.reload();
            });
        }

        // Carrega a URL
        String intentUrl = getIntent().getStringExtra("url");
        if (intentUrl != null && !intentUrl.isEmpty()) {
            webView.loadUrl(intentUrl);
        } else {
            webView.loadUrl(BuildConfig.LOGIN_URL);
        }

        // Inicia worker de notificações (a cada 15 minutos)
        startNotificationWorker();

        // Pede permissão de notificações (Android 13+)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS)
                    != PackageManager.PERMISSION_GRANTED) {
                permissionLauncher.launch(Manifest.permission.POST_NOTIFICATIONS);
            }
        }
    }

    private void startNotificationWorker() {
        Constraints constraints = new Constraints.Builder()
                .setRequiredNetworkType(NetworkType.CONNECTED)
                .build();

        PeriodicWorkRequest workRequest = new PeriodicWorkRequest.Builder(
                NotificationService.class, 15, TimeUnit.MINUTES)
                .setConstraints(constraints)
                .build();

        WorkManager.getInstance(this).enqueueUniquePeriodicWork(
                "infovisa_notifications",
                ExistingPeriodicWorkPolicy.KEEP,
                workRequest
        );
    }

    private void setupWebView() {
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setDatabaseEnabled(true);
        settings.setAllowFileAccess(true);
        settings.setAllowContentAccess(true);
        settings.setLoadWithOverviewMode(true);
        settings.setUseWideViewPort(true);
        settings.setSupportZoom(true);
        settings.setBuiltInZoomControls(true);
        settings.setDisplayZoomControls(false);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE);
        settings.setMediaPlaybackRequiresUserGesture(false);

        // User agent customizado
        String defaultUA = settings.getUserAgentString();
        settings.setUserAgentString(defaultUA + " InfoVISA-App/1.0");

        // Cookies
        CookieManager cookieManager = CookieManager.getInstance();
        cookieManager.setAcceptCookie(true);
        cookieManager.setAcceptThirdPartyCookies(webView, true);

        // Bridge JS
        webView.addJavascriptInterface(new AppBridge(this), "InfoVISAApp");

        // WebViewClient - controla navegação
        webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                String url = request.getUrl().toString();

                // Links externos: WhatsApp, tel, mailto, outros domínios
                if (url.startsWith("tel:") || url.startsWith("mailto:") ||
                        url.startsWith("whatsapp:") || url.contains("wa.me") ||
                        url.contains("api.whatsapp.com")) {
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                    if (intent.resolveActivity(getPackageManager()) != null) {
                        startActivity(intent);
                    }
                    return true;
                }

                // Links do mesmo domínio ficam no WebView
                String baseHost = Uri.parse(BuildConfig.BASE_URL).getHost();
                String linkHost = request.getUrl().getHost();
                if (baseHost != null && baseHost.equals(linkHost)) {
                    return false; // Carrega no WebView
                }

                // Outros links externos abrem no navegador
                Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                startActivity(intent);
                return true;
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                progressBar.setVisibility(View.GONE);
                swipeRefresh.setRefreshing(false);
                injectMobileCSS(view);

                // Quando carrega o dashboard, busca notificações via JS no WebView
                if (url.contains("/company/dashboard") || url.contains("/company/estabelecimentos")) {
                    checkNotifications(view);
                }
            }

            @Override
            public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
                if (request.isForMainFrame()) {
                    webView.setVisibility(View.GONE);
                    errorLayout.setVisibility(View.VISIBLE);
                    TextView errorMsg = findViewById(R.id.errorMessage);
                    if (errorMsg != null) {
                        errorMsg.setText("Erro: " + error.getDescription());
                    }
                }
            }

            @Override
            public void onReceivedSslError(WebView view, android.webkit.SslErrorHandler handler, android.net.http.SslError error) {
                // Em debug, aceita certificados inválidos (localhost)
                if (BuildConfig.DEBUG) {
                    handler.proceed();
                } else {
                    handler.cancel();
                }
            }
        });

        // WebChromeClient - upload de arquivos e progresso
        webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                if (newProgress < 100) {
                    progressBar.setVisibility(View.VISIBLE);
                    progressBar.setProgress(newProgress);
                } else {
                    progressBar.setVisibility(View.GONE);
                }
            }

            @Override
            public boolean onShowFileChooser(WebView webView, ValueCallback<Uri[]> callback,
                                             FileChooserParams fileChooserParams) {
                if (fileUploadCallback != null) {
                    fileUploadCallback.onReceiveValue(null);
                }
                fileUploadCallback = callback;

                Intent chooserIntent = fileChooserParams.createIntent();

                // Adiciona opção de câmera
                try {
                    Intent cameraIntent = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
                    File photoFile = createImageFile();
                    if (photoFile != null) {
                        cameraPhotoUri = FileProvider.getUriForFile(
                                MainActivity.this,
                                getPackageName() + ".fileprovider",
                                photoFile);
                        cameraIntent.putExtra(MediaStore.EXTRA_OUTPUT, cameraPhotoUri);

                        Intent combinedIntent = Intent.createChooser(chooserIntent, "Selecionar arquivo");
                        combinedIntent.putExtra(Intent.EXTRA_INITIAL_INTENTS, new Intent[]{cameraIntent});
                        fileChooserLauncher.launch(combinedIntent);
                        return true;
                    }
                } catch (Exception e) {
                    // Fallback sem câmera
                }

                fileChooserLauncher.launch(chooserIntent);
                return true;
            }
        });
    }

    private void setupSwipeRefresh() {
        swipeRefresh.setColorSchemeColors(
                ContextCompat.getColor(this, R.color.blue_600),
                ContextCompat.getColor(this, R.color.blue_700)
        );
        swipeRefresh.setOnRefreshListener(() -> webView.reload());
    }

    private void injectMobileCSS(WebView view) {
        String css = "javascript:(function(){" +
                "var style=document.createElement('style');" +
                "style.textContent='" +
                // Esconde elementos desnecessários no app
                ".no-app{display:none!important}' +" +
                // Ajusta padding para status bar
                "'body{-webkit-tap-highlight-color:transparent}';" +
                "document.head.appendChild(style);" +
                "})()";
        view.evaluateJavascript(css, null);
    }

    private File createImageFile() {
        try {
            String timeStamp = new SimpleDateFormat("yyyyMMdd_HHmmss", Locale.getDefault()).format(new Date());
            String fileName = "INFOVISA_" + timeStamp;
            File storageDir = getExternalCacheDir();
            return File.createTempFile(fileName, ".jpg", storageDir);
        } catch (IOException e) {
            return null;
        }
    }

    private void requestPermissions() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.READ_MEDIA_IMAGES)
                    != PackageManager.PERMISSION_GRANTED) {
                permissionLauncher.launch(Manifest.permission.READ_MEDIA_IMAGES);
            }
        }
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.CAMERA)
                != PackageManager.PERMISSION_GRANTED) {
            permissionLauncher.launch(Manifest.permission.CAMERA);
        }
    }

    private void checkNotifications(WebView view) {
        String js = "try{" +
                "InfoVISAApp.debugLog('Buscando notificacoes...');" +
                "fetch(window.location.origin + '/company/api/notificacoes', {credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})" +
                ".then(function(r){" +
                "  InfoVISAApp.debugLog('API status: ' + r.status);" +
                "  return r.json();" +
                "})" +
                ".then(function(data){" +
                "  InfoVISAApp.debugLog('Total: ' + data.total);" +
                "  if(data.notificacoes && data.notificacoes.length > 0){" +
                "    data.notificacoes.forEach(function(n){" +
                "      InfoVISAApp.showNotification(n.titulo, n.mensagem, n.tipo, n.url, n.id);" +
                "    });" +
                "  }" +
                "})" +
                ".catch(function(e){ InfoVISAApp.debugLog('Erro: ' + e.message); });" +
                "}catch(ex){InfoVISAApp.debugLog('JS Error: ' + ex.message);}";
        view.evaluateJavascript(js, null);
    }

    @Override
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_BACK && webView.canGoBack()) {
            webView.goBack();
            return true;
        }
        return super.onKeyDown(keyCode, event);
    }

    @Override
    protected void onResume() {
        super.onResume();
        webView.onResume();
    }

    @Override
    protected void onPause() {
        super.onPause();
        webView.onPause();
    }
}
