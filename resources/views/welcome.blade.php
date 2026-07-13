@php
    $defaults = \App\Models\LandingPageSetting::defaultContent();
    $general = array_replace($defaults['general'], $settings->general ?? []);
    $hero = array_replace($defaults['hero'], $settings->hero ?? []);
    $features = array_replace($defaults['features'], $settings->features ?? []);
    $steps = array_replace($defaults['steps'], $settings->steps ?? []);
    $privacy = array_replace($defaults['privacy'], $settings->privacy ?? []);
    $pricing = array_replace($defaults['pricing'], $settings->pricing ?? []);
    $cta = array_replace($defaults['cta'], $settings->cta ?? []);
    $footer = array_replace($defaults['footer'], $settings->footer ?? []);
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $general['seo_title'] }}</title>
    <meta name="description" content="{{ $general['seo_description'] }}">
    <style>
        :root {
            --aral:#0050aa; --aral-bright:#0072ce; --sky:#00a9e0; --ink:#15233b;
            --muted:#6f7a8e; --line:#e8edf4; --soft:#f6f9fd; --success:#16a36a;
        }
        * { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; color:var(--ink); background:#fff; font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; -webkit-font-smoothing:antialiased; }
        a { color:inherit; text-decoration:none; }
        .container { width:min(1140px,calc(100% - 40px)); margin-inline:auto; }
        .nav { height:74px; display:flex; align-items:center; justify-content:space-between; gap:24px; }
        .brand { display:flex; align-items:center; gap:10px; font-size:18px; font-weight:850; letter-spacing:-.03em; }
        .brand img { width:auto; height:38px; object-fit:contain; }
        .brand-mark { width:34px; height:34px; display:grid; place-items:center; color:#fff; border-radius:9px; background:linear-gradient(145deg,var(--sky),var(--aral)); box-shadow:0 8px 18px #0050aa30; }
        .nav-links { display:flex; align-items:center; gap:34px; color:#58657a; font-size:13px; font-weight:650; }
        .nav-actions { display:flex; align-items:center; gap:10px; }
        .button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:0 18px; border:1px solid transparent; border-radius:9px; font-size:13px; font-weight:800; transition:transform .18s ease,box-shadow .18s ease,background .18s ease; }
        .button:hover { transform:translateY(-1px); }
        .button-primary { color:#fff; background:linear-gradient(135deg,var(--aral-bright),var(--aral)); box-shadow:0 10px 24px #0050aa2d; }
        .button-secondary { color:#344158; background:#fff; border-color:#dfe6ef; box-shadow:0 4px 14px #1d35570a; }
        .button-light { color:var(--aral); background:#fff; box-shadow:0 8px 22px #002b6b30; }
        .button-ghost-light { color:#fff; border-color:#ffffff55; background:#ffffff0d; }

        .hero { position:relative; overflow:hidden; min-height:660px; padding:72px 0 0; text-align:center; background:radial-gradient(circle at 50% 35%,#ffffff 0,#f4f8ff 42%,#edf5ff 100%); }
        .hero::before { content:""; position:absolute; inset:auto auto -240px -180px; width:540px; height:540px; border-radius:50%; background:#00a9e012; filter:blur(10px); }
        .badge { display:inline-flex; align-items:center; gap:7px; padding:7px 12px; color:var(--aral); background:#eaf4ff; border:1px solid #d6eaff; border-radius:999px; font-size:11px; font-weight:800; }
        .hero h1 { max-width:780px; margin:20px auto 16px; font-size:clamp(45px,6vw,72px); line-height:1.02; letter-spacing:-.055em; }
        .gradient-text { display:block; color:var(--aral); background:linear-gradient(100deg,var(--aral),var(--sky)); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
        .hero-copy { max-width:650px; margin:0 auto; color:var(--muted); font-size:16px; line-height:1.65; }
        .hero-actions { display:flex; justify-content:center; flex-wrap:wrap; gap:12px; margin-top:26px; }
        .trust { display:flex; justify-content:center; flex-wrap:wrap; gap:20px; margin:20px 0 42px; color:#7b8798; font-size:11px; }
        .trust span::before { content:"✓"; margin-right:6px; color:var(--success); font-weight:900; }
        .dashboard { position:relative; z-index:2; max-width:860px; margin:auto; padding:18px 20px 24px; text-align:left; border:1px solid #e0e8f3; border-radius:18px 18px 0 0; background:#fff; box-shadow:0 28px 70px #12477a20; }
        .browserbar { height:20px; display:flex; align-items:center; gap:6px; margin-bottom:16px; }
        .browserbar i { width:7px; height:7px; border-radius:50%; background:#ff7979; }
        .browserbar i:nth-child(2) { background:#ffc968; } .browserbar i:nth-child(3) { background:#68d68e; }
        .address { height:8px; flex:1; margin-left:12px; border-radius:10px; background:#f0f3f7; }
        .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
        .stat { padding:17px; border-radius:11px; background:#f5f9ff; }
        .stat[data-tone="green"] { background:#f1fbf6; } .stat[data-tone="yellow"] { background:#fffaf0; }
        .stat[data-tone="purple"],.stat[data-tone="indigo"] { background:#f1f6ff; }
        .stat-label { color:#6c7789; font-size:10px; font-weight:700; }
        .stat-value { display:block; margin:7px 0 3px; font-size:26px; line-height:1; font-weight:850; }
        .stat-note { color:var(--aral-bright); font-size:9px; }
        .dashboard-line { height:38px; margin-top:14px; border:1px solid #edf1f6; border-radius:9px; background:linear-gradient(90deg,#fafcff,#fff); }

        .section { padding:110px 0; }
        .section-soft { background:#f8fafc; }
        .section-head { max-width:720px; margin:0 auto 52px; text-align:center; }
        .kicker { display:block; margin-bottom:10px; color:var(--aral-bright); text-transform:uppercase; letter-spacing:.13em; font-size:10px; font-weight:900; }
        .section h2 { margin:0; font-size:clamp(30px,4vw,44px); line-height:1.15; letter-spacing:-.04em; }
        .section-head p { margin:14px auto 0; color:var(--muted); line-height:1.65; font-size:14px; }
        .feature-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
        .feature-card { min-height:210px; padding:24px; border:1px solid var(--line); border-radius:14px; background:#fff; box-shadow:0 8px 30px #18375d08; transition:.2s ease; }
        .feature-card:hover { transform:translateY(-3px); border-color:#cddff4; box-shadow:0 18px 38px #16497912; }
        .icon { width:38px; height:38px; display:grid; place-items:center; color:var(--aral); border-radius:9px; background:#e9f4ff; font-weight:900; }
        .feature-card[data-tone="green"] .icon { color:#12805a; background:#e8faf2; }
        .feature-card[data-tone="yellow"] .icon { color:#ad7200; background:#fff5d9; }
        .feature-card[data-tone="pink"] .icon { color:#c44568; background:#fff0f4; }
        .feature-card h3 { margin:18px 0 8px; font-size:15px; }
        .feature-card p { margin:0; color:var(--muted); font-size:12px; line-height:1.65; }

        .steps { position:relative; display:grid; grid-template-columns:repeat(3,1fr); gap:70px; text-align:center; }
        .steps::before { content:""; position:absolute; top:23px; left:17%; right:17%; height:1px; background:#dce6f3; }
        .step { position:relative; z-index:1; }
        .step-number { width:48px; height:48px; display:grid; place-items:center; margin:0 auto 20px; color:#fff; border-radius:12px; background:linear-gradient(145deg,var(--sky),var(--aral)); box-shadow:0 10px 22px #0050aa30; font-size:16px; font-weight:900; }
        .step h3 { margin:0 0 9px; font-size:15px; }
        .step p { margin:0; color:var(--muted); font-size:12px; line-height:1.65; }

        .privacy-wrap { display:grid; grid-template-columns:1.2fr .8fr; gap:90px; align-items:center; }
        .privacy-copy h2 { margin-bottom:15px; }
        .privacy-intro { color:var(--muted); line-height:1.65; }
        .privacy-list { display:grid; gap:18px; margin-top:30px; }
        .privacy-item { display:grid; grid-template-columns:22px 1fr; gap:10px; }
        .check { width:19px; height:19px; display:grid; place-items:center; color:#fff; border-radius:50%; background:var(--success); font-size:10px; font-weight:900; }
        .privacy-item strong { display:block; margin-bottom:3px; font-size:13px; }
        .privacy-item p { margin:0; color:var(--muted); font-size:11px; line-height:1.5; }
        .status-card { max-width:360px; padding:26px; border:1px solid var(--line); border-radius:16px; background:#fff; box-shadow:0 24px 50px #18375d18; }
        .status-title { display:flex; align-items:center; gap:10px; padding-bottom:17px; border-bottom:1px solid #edf0f5; font-weight:850; }
        .status-row { display:flex; justify-content:space-between; gap:20px; padding-top:14px; color:#596579; font-size:12px; }
        .status-pill { color:#087b4f; padding:3px 7px; border-radius:999px; background:#eaf9f1; font-size:10px; font-weight:850; }

        .pricing-box { position:relative; width:min(430px,100%); margin:auto; padding:34px; border:2px solid var(--aral-bright); border-radius:14px; background:#fff; box-shadow:0 18px 50px #0050aa14; }
        .pricing-badge { position:absolute; top:-13px; left:50%; transform:translateX(-50%); padding:6px 18px; color:#fff; border-radius:999px; background:var(--aral); font-size:10px; font-weight:850; white-space:nowrap; }
        .plan-name { text-align:center; font-weight:850; }
        .price { display:block; margin:15px 0 8px; text-align:center; font-size:42px; line-height:1; font-weight:900; letter-spacing:-.05em; }
        .price-note { margin:0 0 24px; color:var(--muted); text-align:center; font-size:11px; }
        .plan-list { display:grid; gap:10px; margin:0 0 25px; padding:0; list-style:none; color:#536075; font-size:12px; }
        .plan-list li::before { content:"✓"; margin-right:9px; color:var(--aral-bright); font-weight:900; }
        .pricing-box .button { width:100%; }

        .cta-section { padding:100px 0; }
        .cta-box { padding:58px 40px; color:#fff; text-align:center; border-radius:19px; background:linear-gradient(120deg,var(--aral),var(--aral-bright) 65%,var(--sky)); box-shadow:0 24px 55px #0050aa35; }
        .cta-box h2 { margin:0; color:#fff; font-size:34px; }
        .cta-box p { max-width:620px; margin:14px auto 26px; color:#e8f5ff; line-height:1.6; font-size:13px; }
        .cta-actions { display:flex; justify-content:center; flex-wrap:wrap; gap:11px; }

        footer { padding:50px 0 22px; background:#f7f9fc; border-top:1px solid #edf0f4; }
        .footer-grid { display:grid; grid-template-columns:1.4fr repeat(3,1fr); gap:60px; padding-bottom:40px; }
        .footer-about p { max-width:280px; color:var(--muted); font-size:11px; line-height:1.65; }
        .footer-column strong { display:block; margin-bottom:15px; font-size:12px; }
        .footer-column a { display:block; margin-top:10px; color:var(--muted); font-size:11px; }
        .footer-bottom { padding-top:20px; color:#99a2b1; text-align:center; border-top:1px solid #e8edf3; font-size:10px; }

        @media (max-width:900px) {
            .nav-links { display:none; } .hero { padding-top:55px; } .stat-grid { grid-template-columns:repeat(2,1fr); }
            .feature-grid { grid-template-columns:repeat(2,1fr); } .privacy-wrap { grid-template-columns:1fr; gap:50px; }
            .status-card { max-width:none; } .footer-grid { grid-template-columns:repeat(2,1fr); }
        }
        @media (max-width:620px) {
            .container { width:min(100% - 28px,1140px); } .nav { height:66px; } .nav-actions .button-secondary { display:none; }
            .hero { min-height:auto; } .hero h1 { font-size:43px; } .dashboard { padding:13px; } .stat-grid { gap:7px; }
            .stat { padding:13px; } .section { padding:80px 0; } .feature-grid,.steps { grid-template-columns:1fr; }
            .steps { gap:36px; } .steps::before { display:none; } .footer-grid { grid-template-columns:1fr 1fr; gap:38px 25px; }
            .footer-about { grid-column:1/-1; } .cta-box { padding:44px 20px; } .cta-box h2 { font-size:28px; }
        }
    </style>
</head>
<body>
    {{-- Das visuelle Grundgerüst ist fest programmiert; sämtliche sichtbaren Inhalte stammen aus der Adminpflege. --}}
    <header class="container nav">
        <a class="brand" href="{{ route('landing') }}">
            @if(filled($general['logo_path']))
                <img src="{{ asset('storage/'.$general['logo_path']) }}" alt="{{ $general['site_name'] }}">
            @else
                <span class="brand-mark">S</span><span>{{ $general['site_name'] }}</span>
            @endif
        </a>
        <nav class="nav-links" aria-label="Hauptnavigation">
            <a href="#funktionen">{{ $general['nav_features'] }}</a>
            <a href="#ablauf">{{ $general['nav_process'] }}</a>
            <a href="#preise">{{ $general['nav_pricing'] }}</a>
        </nav>
        <div class="nav-actions">
            <a class="button button-secondary" href="{{ route('filament.partner.auth.login') }}">{{ $general['login_label'] }}</a>
            <a class="button button-primary" href="{{ route('filament.partner.auth.register') }}">{{ $general['register_label'] }}</a>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <span class="badge">✦ {{ $hero['badge'] }}</span>
                <h1>{{ $hero['title_before'] }} <span class="gradient-text">{{ $hero['highlight'] }}</span></h1>
                <p class="hero-copy">{{ $hero['description'] }}</p>
                <div class="hero-actions">
                    <a class="button button-primary" href="{{ route('filament.partner.auth.register') }}">{{ $hero['primary_label'] }} →</a>
                    <a class="button button-secondary" href="#funktionen">{{ $hero['secondary_label'] }}</a>
                </div>
                <div class="trust">
                    @foreach($hero['trust_items'] ?? [] as $item)<span>{{ $item }}</span>@endforeach
                </div>
                <div class="dashboard">
                    <div class="browserbar"><i></i><i></i><i></i><span class="address"></span></div>
                    <div class="stat-grid">
                        @foreach($hero['stats'] ?? [] as $stat)
                            <article class="stat" data-tone="{{ $stat['tone'] ?? 'blue' }}">
                                <span class="stat-label">{{ $stat['label'] ?? '' }}</span>
                                <strong class="stat-value">{{ $stat['value'] ?? '' }}</strong>
                                <span class="stat-note">{{ $stat['note'] ?? '' }}</span>
                            </article>
                        @endforeach
                    </div>
                    <div class="dashboard-line"></div>
                </div>
            </div>
        </section>

        <section class="section" id="funktionen">
            <div class="container">
                <header class="section-head"><span class="kicker">{{ $features['kicker'] }}</span><h2>{{ $features['title'] }}</h2><p>{{ $features['description'] }}</p></header>
                <div class="feature-grid">
                    @foreach($features['items'] ?? [] as $feature)
                        <article class="feature-card" data-tone="{{ $feature['tone'] ?? 'blue' }}">
                            <span class="icon">{{ mb_strtoupper(mb_substr($feature['title'] ?? 'F', 0, 1)) }}</span>
                            <h3>{{ $feature['title'] ?? '' }}</h3><p>{{ $feature['description'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section section-soft" id="ablauf">
            <div class="container">
                <header class="section-head"><span class="kicker">{{ $steps['kicker'] }}</span><h2>{{ $steps['title'] }}</h2></header>
                <div class="steps">
                    @foreach($steps['items'] ?? [] as $step)
                        <article class="step"><span class="step-number">{{ $step['number'] ?? $loop->iteration }}</span><h3>{{ $step['title'] ?? '' }}</h3><p>{{ $step['description'] ?? '' }}</p></article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section" id="datenschutz">
            <div class="container privacy-wrap">
                <div class="privacy-copy">
                    <span class="kicker">{{ $privacy['kicker'] }}</span><h2>{{ $privacy['title'] }}</h2><p class="privacy-intro">{{ $privacy['description'] }}</p>
                    <div class="privacy-list">
                        @foreach($privacy['points'] ?? [] as $point)
                            <article class="privacy-item"><span class="check">✓</span><div><strong>{{ $point['title'] ?? '' }}</strong><p>{{ $point['description'] ?? '' }}</p></div></article>
                        @endforeach
                    </div>
                </div>
                <aside class="status-card"><div class="status-title"><span class="icon">✓</span> Datenschutz-Status</div>
                    @foreach($privacy['status_items'] ?? [] as $status)
                        <div class="status-row"><span>{{ $status['label'] ?? '' }}</span><span class="status-pill">{{ $status['status'] ?? '' }}</span></div>
                    @endforeach
                </aside>
            </div>
        </section>

        <section class="section section-soft" id="preise">
            <div class="container">
                <header class="section-head"><span class="kicker">{{ $pricing['kicker'] }}</span><h2>{{ $pricing['title'] }}</h2><p>{{ $pricing['description'] }}</p></header>
                <div class="pricing-box"><span class="pricing-badge">{{ $pricing['badge'] }}</span><div class="plan-name">{{ $pricing['plan_name'] }}</div><strong class="price">{{ $pricing['price'] }}</strong><p class="price-note">{{ $pricing['price_note'] }}</p>
                    <ul class="plan-list">@foreach($pricing['features'] ?? [] as $item)<li>{{ $item }}</li>@endforeach</ul>
                    <a class="button button-primary" href="{{ route('filament.partner.auth.register') }}">{{ $pricing['button_label'] }} →</a>
                </div>
            </div>
        </section>

        <section class="cta-section"><div class="container"><div class="cta-box"><h2>{{ $cta['title'] }}</h2><p>{{ $cta['description'] }}</p><div class="cta-actions"><a class="button button-light" href="{{ route('filament.partner.auth.register') }}">{{ $cta['primary_label'] }}</a><a class="button button-ghost-light" href="{{ route('filament.partner.auth.login') }}">{{ $cta['secondary_label'] }}</a></div></div></div></section>
    </main>

    <footer>
        <div class="container footer-grid">
            <div class="footer-about"><a class="brand" href="{{ route('landing') }}"><span class="brand-mark">S</span><span>{{ $general['site_name'] }}</span></a><p>{{ $footer['description'] }}</p></div>
            @foreach($footer['columns'] ?? [] as $column)
                <div class="footer-column"><strong>{{ $column['title'] ?? '' }}</strong>@foreach($column['links'] ?? [] as $link)<a href="{{ $link['url'] ?? '#' }}">{{ $link['label'] ?? '' }}</a>@endforeach</div>
            @endforeach
        </div>
        <div class="container footer-bottom">{{ $footer['copyright'] }}</div>
    </footer>
</body>
</html>
