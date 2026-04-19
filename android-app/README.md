# InfoVISA - App Android

Aplicativo Android nativo (WebView wrapper) para o sistema InfoVISA - Área do Estabelecimento.

## Estrutura

```
android-app/
├── app/src/main/
│   ├── java/br/gov/to/saude/infovisa/
│   │   ├── SplashActivity.java    → Tela de splash com verificação de conexão
│   │   ├── MainActivity.java      → WebView principal com upload e navegação
│   │   └── AppBridge.java         → Bridge JS (window.InfoVISAApp)
│   ├── res/
│   │   ├── layout/                → Layouts XML
│   │   ├── values/                → Cores, strings, temas
│   │   └── xml/                   → Network security, file paths
│   └── AndroidManifest.xml
├── build.gradle                   → Config do projeto
└── app/build.gradle               → Config do app (URLs, SDK)
```

## Como abrir no Android Studio

1. Abra o Android Studio
2. File → Open → selecione a pasta `android-app/`
3. Aguarde o Gradle sincronizar
4. Run → selecione emulador ou dispositivo

## Configuração de URLs

Edite `app/build.gradle`:

- **Debug** (emulador): usa `http://10.0.2.2:8000` (localhost do host)
- **Release** (produção): usa `https://sistemas.saude.to.gov.br/infovisacore`

## Funcionalidades

- Splash screen com verificação de conexão
- WebView com JavaScript, DOM Storage, cookies
- Upload de arquivos (galeria + câmera)
- Swipe-to-refresh
- Links externos (WhatsApp, tel, mailto) abrem no navegador
- Botão voltar navega no histórico
- Tela de erro offline com retry
- Bridge JS: `window.InfoVISAApp.isApp()`, `.getAppVersion()`, etc.
- Barra de progresso no topo

## Gerar APK

No Android Studio: Build → Build Bundle(s) / APK(s) → Build APK(s)

O APK fica em: `app/build/outputs/apk/release/app-release.apk`

## Ícone

Substitua os arquivos em `app/src/main/res/mipmap-*/` com o ícone do InfoVISA.
Use o Asset Studio do Android Studio: File → New → Image Asset
