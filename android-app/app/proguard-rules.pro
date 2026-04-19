# WebView
-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}
-keepattributes JavascriptInterface

# Keep BuildConfig
-keep class br.gov.to.saude.infovisa.BuildConfig { *; }
