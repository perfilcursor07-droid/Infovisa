package br.gov.to.saude.infovisa;

import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Build;
import android.util.Log;

import androidx.annotation.NonNull;
import androidx.core.app.NotificationCompat;
import androidx.work.Worker;
import androidx.work.WorkerParameters;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.CookieHandler;
import java.net.CookieManager;
import java.net.HttpURLConnection;
import java.net.URL;

public class NotificationService extends Worker {

    private static final String TAG = "NotificationService";
    private static final String CHANNEL_ID = "infovisa_notifications";
    private static final String CHANNEL_NAME = "InfoVISA Notificações";
    private static final String PREFS_NAME = "infovisa_notif_prefs";

    public NotificationService(@NonNull Context context, @NonNull WorkerParameters params) {
        super(context, params);
    }

    @NonNull
    @Override
    public Result doWork() {
        try {
            String baseUrl = BuildConfig.BASE_URL;
            String apiUrl = baseUrl + "/company/api/notificacoes";

            // Pega o cookie de sessão salvo pelo WebView
            String cookies = android.webkit.CookieManager.getInstance().getCookie(baseUrl);

            if (cookies == null || cookies.isEmpty()) {
                Log.d(TAG, "Sem cookies de sessão, usuário não logado");
                return Result.success();
            }

            URL url = new URL(apiUrl);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");
            conn.setRequestProperty("Cookie", cookies);
            conn.setRequestProperty("Accept", "application/json");
            conn.setRequestProperty("X-Requested-With", "XMLHttpRequest");
            conn.setConnectTimeout(15000);
            conn.setReadTimeout(15000);

            int responseCode = conn.getResponseCode();
            if (responseCode != 200) {
                Log.d(TAG, "API retornou " + responseCode);
                return Result.success();
            }

            BufferedReader reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
            StringBuilder response = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null) {
                response.append(line);
            }
            reader.close();

            JSONObject json = new JSONObject(response.toString());
            JSONArray notificacoes = json.getJSONArray("notificacoes");

            SharedPreferences prefs = getApplicationContext().getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
            SharedPreferences.Editor editor = prefs.edit();

            createNotificationChannel();

            int notifCount = 0;
            for (int i = 0; i < notificacoes.length() && notifCount < 5; i++) {
                JSONObject notif = notificacoes.getJSONObject(i);
                String notifId = notif.getString("id");

                // Só notifica se ainda não foi mostrada
                if (prefs.getBoolean("shown_" + notifId, false)) {
                    continue;
                }

                showNotification(
                        notifId.hashCode(),
                        notif.getString("titulo"),
                        notif.getString("mensagem"),
                        notif.getString("tipo"),
                        notif.getString("url")
                );

                editor.putBoolean("shown_" + notifId, true);
                notifCount++;
            }

            editor.apply();

            // Limpa notificações antigas (mais de 7 dias)
            // Isso evita que o SharedPreferences cresça indefinidamente
            // Implementação simplificada: limpa tudo a cada 1000 entradas
            if (prefs.getAll().size() > 1000) {
                editor.clear().apply();
            }

            return Result.success();

        } catch (Exception e) {
            Log.e(TAG, "Erro ao buscar notificações", e);
            return Result.retry();
        }
    }

    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID,
                    CHANNEL_NAME,
                    NotificationManager.IMPORTANCE_DEFAULT
            );
            channel.setDescription("Notificações do sistema InfoVISA");
            channel.enableVibration(true);

            NotificationManager manager = getApplicationContext().getSystemService(NotificationManager.class);
            if (manager != null) {
                manager.createNotificationChannel(channel);
            }
        }
    }

    private void showNotification(int id, String title, String message, String tipo, String urlPath) {
        Context context = getApplicationContext();

        Intent intent = new Intent(context, MainActivity.class);
        intent.putExtra("url", BuildConfig.BASE_URL + urlPath);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);

        PendingIntent pendingIntent = PendingIntent.getActivity(
                context, id, intent,
                PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE
        );

        // Ícone e cor baseados no tipo
        int color;
        switch (tipo) {
            case "estabelecimento_aprovado":
                color = 0xFF16A34A; // green
                break;
            case "estabelecimento_rejeitado":
            case "documento_rejeitado":
                color = 0xFFDC2626; // red
                break;
            case "documento_prazo":
                color = 0xFFF59E0B; // amber
                break;
            default:
                color = 0xFF2563EB; // blue
                break;
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
    }
}
