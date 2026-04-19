package br.gov.to.saude.infovisa;

import android.content.Context;
import android.content.pm.PackageInfo;
import android.os.Build;
import android.webkit.JavascriptInterface;

/**
 * Bridge JavaScript para comunicação entre o site e o app.
 * No site, acesse via: window.InfoVISAApp.getAppVersion()
 */
public class AppBridge {

    private final Context context;

    public AppBridge(Context context) {
        this.context = context;
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
}
