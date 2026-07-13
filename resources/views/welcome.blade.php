<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Station Desk – Tankstellen einfach führen</title>
    <meta name="description" content="Die moderne Arbeitsplattform für Tankstellenpartner, Stationsleiter und Teams.">
    <style>
        :root { color-scheme: light; --ink:#10213f; --muted:#61708c; --blue:#1769ff; --cyan:#1cc8c8; --paper:#f5f8ff; }
        * { box-sizing: border-box; }
        body { margin:0; color:var(--ink); background:var(--paper); font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; }
        a { color:inherit; text-decoration:none; }
        .shell { width:min(1180px,calc(100% - 40px)); margin:auto; }
        header { display:flex; align-items:center; justify-content:space-between; padding:26px 0; }
        .brand { display:flex; gap:12px; align-items:center; font-weight:850; font-size:20px; letter-spacing:-.03em; }
        .mark { width:38px; height:38px; display:grid; place-items:center; border-radius:12px; color:white; background:linear-gradient(135deg,var(--blue),var(--cyan)); box-shadow:0 10px 28px #1769ff33; }
        nav { display:flex; align-items:center; gap:14px; }
        .button { display:inline-flex; align-items:center; justify-content:center; padding:13px 20px; border-radius:12px; font-weight:750; transition:.2s ease; }
        .button:hover { transform:translateY(-1px); }
        .primary { color:white; background:var(--blue); box-shadow:0 12px 28px #1769ff35; }
        .secondary { background:white; border:1px solid #dfe6f3; }
        .hero { min-height:620px; display:grid; grid-template-columns:1.15fr .85fr; gap:70px; align-items:center; padding:72px 0 110px; }
        .eyebrow { display:inline-flex; padding:8px 12px; border-radius:999px; color:#075f77; background:#dff8fa; font-size:13px; font-weight:800; }
        h1 { max-width:760px; margin:22px 0; font-size:clamp(46px,6vw,78px); line-height:.98; letter-spacing:-.065em; }
        .lead { max-width:650px; margin:0 0 32px; color:var(--muted); font-size:19px; line-height:1.7; }
        .actions { display:flex; flex-wrap:wrap; gap:12px; }
        .note { margin-top:18px; color:var(--muted); font-size:13px; }
        .preview { position:relative; padding:18px; border:1px solid #dfe8f7; border-radius:28px; background:#fff; box-shadow:0 35px 90px #274a8430; transform:rotate(1.5deg); }
        .topline { display:flex; justify-content:space-between; align-items:center; padding:8px 6px 20px; }
        .dots { color:#a8b5ca; letter-spacing:3px; }
        .status { color:#087d5f; background:#e5fbf3; padding:7px 10px; border-radius:999px; font-size:12px; font-weight:800; }
        .cards { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
        .card { min-height:140px; padding:20px; border-radius:18px; background:#f7f9fd; }
        .card strong { display:block; margin-top:22px; font-size:30px; }
        .card span { color:var(--muted); font-size:13px; }
        .card.wide { grid-column:1/-1; min-height:120px; color:white; background:linear-gradient(130deg,#103d8e,#1769ff 65%,#1cc8c8); }
        .features { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; padding-bottom:90px; }
        .feature { padding:26px; border:1px solid #e1e8f4; border-radius:20px; background:#fff; }
        .feature h2 { margin:14px 0 9px; font-size:18px; }
        .feature p { margin:0; color:var(--muted); line-height:1.65; }
        @media (max-width:850px) { .hero { grid-template-columns:1fr; gap:45px; padding-top:40px; } .features { grid-template-columns:1fr; } nav .secondary { display:none; } }
    </style>
</head>
<body>
    {{-- Neutrale Plattform-Landingpage; Markenfarben werden erst im gewählten Tankstellen-Kontext geladen. --}}
    <div class="shell">
        <header>
            <a class="brand" href="/"><span class="mark">S</span> Station Desk</a>
            <nav>
                <a class="button secondary" href="{{ route('filament.partner.auth.login') }}">Anmelden</a>
                <a class="button primary" href="{{ route('filament.partner.auth.register') }}">Kostenlos starten</a>
            </nav>
        </header>

        <main>
            <section class="hero">
                <div>
                    <span class="eyebrow">Eine Plattform. Alle Tankstellen. Jeder Arbeitstag.</span>
                    <h1>Tankstellen führen, ohne den Überblick zu verlieren.</h1>
                    <p class="lead">Station Desk verbindet Partner, Stationsleiter und Teams – mit klaren Aufgaben, sicheren Zugängen und allen Standorten in einer modernen Arbeitsoberfläche.</p>
                    <div class="actions">
                        <a class="button primary" href="{{ route('filament.partner.auth.register') }}">30 Tage kostenlos testen</a>
                        <a class="button secondary" href="{{ route('filament.partner.auth.login') }}">Zum Partner-Login</a>
                    </div>
                    <p class="note">Keine Zahlungsdaten für die Testphase erforderlich.</p>
                </div>

                <div class="preview" aria-label="Vorschau des Partner-Dashboards">
                    <div class="topline"><strong>Guten Morgen</strong><span class="status">Alle Systeme aktiv</span></div>
                    <div class="cards">
                        <div class="card"><span>Tankstellen</span><strong>2</strong></div>
                        <div class="card"><span>Mitarbeiter</span><strong>28</strong></div>
                        <div class="card wide"><span>Heute im Blick</span><strong>94 % erledigt</strong></div>
                    </div>
                </div>
            </section>

            <section class="features">
                <article class="feature"><span>01</span><h2>Alle Standorte im Blick</h2><p>Partner und Vertreter sehen Tankstellen, Teams und wichtige Vorgänge in einer gemeinsamen Übersicht.</p></article>
                <article class="feature"><span>02</span><h2>Sicher am MDE arbeiten</h2><p>QR-Code, GPS-Prüfung, NFC und persönliche Anmeldung sorgen für einen klaren Tankstellen-Kontext.</p></article>
                <article class="feature"><span>03</span><h2>Wächst mit deinen Modulen</h2><p>Tagesaufgaben, MHD, Abschriften und weitere Arbeitsbereiche werden Schritt für Schritt ergänzt.</p></article>
            </section>
        </main>
    </div>
</body>
</html>
