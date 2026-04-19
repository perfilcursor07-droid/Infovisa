package br.gov.to.saude.infovisa;

import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

public class SplashActivity extends AppCompatActivity {

    private static final int SPLASH_DELAY = 2000;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash);

        ProgressBar progressBar = findViewById(R.id.splashProgress);
        TextView statusText = findViewById(R.id.splashStatus);

        if (progressBar != null) progressBar.setVisibility(View.VISIBLE);
        if (statusText != null) statusText.setText("Verificando conexão...");

        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            if (isNetworkAvailable()) {
                if (statusText != null) statusText.setText("Conectado! Carregando...");
                new Handler(Looper.getMainLooper()).postDelayed(this::goToMain, 500);
            } else {
                if (statusText != null) statusText.setText("Sem conexão com a internet");
                if (progressBar != null) progressBar.setVisibility(View.GONE);
                // Tenta novamente após 3 segundos
                new Handler(Looper.getMainLooper()).postDelayed(() -> {
                    if (isNetworkAvailable()) {
                        goToMain();
                    } else {
                        if (statusText != null) statusText.setText("Verifique sua conexão e reabra o app");
                    }
                }, 3000);
            }
        }, SPLASH_DELAY);
    }

    private void goToMain() {
        Intent intent = new Intent(this, MainActivity.class);
        startActivity(intent);
        finish();
        overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
    }

    private boolean isNetworkAvailable() {
        ConnectivityManager cm = (ConnectivityManager) getSystemService(CONNECTIVITY_SERVICE);
        if (cm == null) return false;
        NetworkInfo activeNetwork = cm.getActiveNetworkInfo();
        return activeNetwork != null && activeNetwork.isConnected();
    }
}
