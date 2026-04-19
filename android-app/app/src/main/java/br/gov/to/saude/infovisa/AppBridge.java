package br.gov.to.saude.infovisa;

import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageInfo;
import android.os.Build;
import android.webkit.JavascriptInterface;

import androidx.core.app.NotificationCompat;

/**
 * Bridge JavaScript para comunicação entre o site e o app.
 * No site, acesse via: window.InfoVISAApp.getAppVersion()
 */
public class AppBridge {

    private static final String CHANNEL_ID = "infovisa_notifications";
    private static final String PREFS_NAME = "infovisa_notif_prefs";
    private final Context context;

    public AppBridge(Context context) {
        this.context = context;
        createNotificationChannel();
    }

    @JavascriptInterface
    public boolean isApp() {
        return true;
    }

    @JavascriptInterface
    public String getAppVersion() {
        try {
            PackageInfo pInfo = context.getPackageManager().getPackageInfo(context.getPackageName(), 0);
            return pInfo.versionName;
        } catch (Exception e) {
            return "1.0.0";
        }
    }

    @JavascriptInterface
    public String getDeviceModel() {
        return Build.MANUFACTURER + " " + Build.MODEL;
    }

    @JavascriptInterface
    public String getAndroidVersion() {
        return "Android " + Build.VERSION.RELEASE + " (API " + Build.VERSION.SDK_INT + ")";
    }

    @JavascriptInterface
    public String getPlatform() {
        return "android";
    }

    @JavascriptInterface
    public void debugLog(String msg) {
        android.os.Handler mainHandler = new android.os.Handler(android.os.Looper.getMainLooper());
        mainHandler.post(() -> android.widget.Toast.makeText(context, msg, android.widget.Toast.LENGTH_LONG).show());
    }

    @JavascriptInterface
    public void showNotification(String title, String message, String tipo, String urlPath, String notifId) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);

        // Não repete notificação já mostrada
        if (prefs.getBoolean("shown_" + notifId, false)) {
            return;
        }

        int id = notifId.hashCode();

        Intent intent = new Intent(context, MainActivity.class);
        intent.putExtra("url", BuildConfig.BASE_URL + urlPath);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);

        PendingIntent pendingIntent = PendingIntent.getActivity(
                context, id, intent,
                PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE
        );

        int color;
        switch (tipo) {
            case "estabelecimento_aprovado": color = 0xFF16A34A; break;
            case "estabelecimento_rejeitado":
            case "documento_rejeitado": color = 0xFFDC2626; break;
            case "documento_prazo": color = 0xFFF59E0B; break;
            default: color = 0xFF2563EB; break;
        }

        Notification notification = new NotificationCompat.Builder(context, CHANNEL_ID)
                .setSmallIcon(android.R.drawable.ic_dialog_info)
                .setContentTitle(title)
                .setContentText(message)
                .setStyle(new NotificationCompat.BigTextStyle().bigText(message))
                .setColor(color)
                .setAutoCancel(true)
                .setContentIntent(pendingIntent)
                .setPriority(NotificationCompat.PRIORITY_DEFAULT)
                .build();

        NotificationManager manager = (NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);
        if (manager != null) {
            manager.notify(id, notification);
        }

        prefs.edit().putBoolean("shown_" + notifId, true).apply();
    }

    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID, "InfoVISA Notificações",
                    NotificationManager.IMPORTANCE_DEFAULT
            );
            channel.setDescription("Notificações do sistema InfoVISA");
            channel.enableVibration(true);
            NotificationManager manager = context.getSystemService(NotificationManager.class);
            if (manager != null) {
                manager.createNotificationChannel(channel);
            }
        }
    }
}
