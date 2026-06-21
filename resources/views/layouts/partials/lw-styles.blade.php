@include('layouts.partials.lw-theme-vars')
<style>
/* ——— Tipografi ——— */
.lw-shell,.lw-panel-body{font-family:"Instrument Sans",ui-sans-serif,system-ui,sans-serif}
.lw-section-title,.lw-hero-title,.lw-panel-page-title,.lw-rt-page-title{font-weight:800;letter-spacing:-.02em}
.lw-hero-lead,.lw-section-desc,.lw-services-catalog-lead,.lw-rt-page-lead{font-size:1rem;line-height:1.65;font-weight:400;color:var(--lw-text-secondary)}
.lw-section-tag,.lw-hero-eyebrow,.lw-rt-dash-eyebrow{font-weight:600;letter-spacing:.08em;text-transform:uppercase}
.lw-panel-topbar-title{font-weight:700;font-size:1rem;color:var(--lw-text-strong);margin:0;line-height:1.3}
.lw-alert{margin-bottom:1rem;border-radius:.75rem;padding:.75rem 1rem;font-size:.875rem;line-height:1.5}
.lw-alert--success{background:var(--lw-alert-success-bg);border:1px solid var(--lw-alert-success-border);color:var(--lw-alert-success-text)}
.lw-alert--error{background:var(--lw-alert-error-bg);border:1px solid var(--lw-alert-error-border);color:var(--lw-alert-error-text)}
.lw-alert--warn{background:var(--lw-stat-warn);border:1px solid #fde68a;color:#92400e}
.lw-alert--info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}
.lw-text-left{text-align:left}
.lw-alert--error ul{margin:0;padding-left:1.25rem}
.lw-alert-list{margin:0;padding-left:1.25rem;list-style:disc}
.lw-alert-list li+li{margin-top:.25rem}
.lw-surface{background:var(--lw-bg-surface);border:1px solid var(--lw-border);border-radius:.75rem}
.lw-surface-muted{background:var(--lw-bg-surface-muted);border:1px solid var(--lw-border-soft);border-radius:.5rem}

/* ——— Layout beranda publik ——— */
.lw-shell{min-height:100vh;display:flex;flex-direction:column;background:var(--lw-bg-shell);color:var(--lw-text);-webkit-font-smoothing:antialiased;padding:0;box-sizing:border-box}
.lw-shell>.lw-main{flex:1}
.lw-site-frame{flex:1;display:flex;flex-direction:column;width:100%;max-width:var(--lw-content-max);margin-inline:auto;min-width:0;box-sizing:border-box;background:var(--lw-bg-card);border:1px solid var(--lw-border-soft)}
@media(min-width:1024px){.lw-site-frame{border-radius:var(--lw-frame-radius);box-shadow:var(--lw-shadow-md)}}
.lw-main{flex:1;width:100%;padding:1.25rem var(--lw-content-gutter) 2rem;box-sizing:border-box}
@media(min-width:640px){.lw-main{padding-top:2rem;padding-bottom:2rem}}
.lw-container{width:100%;max-width:100%;margin-inline:auto;box-sizing:border-box}
.lw-container--narrow{max-width:48rem}
.lw-container--wide{width:100%;max-width:72rem;margin-inline:auto}
.lw-band--alt{width:100%;background:var(--lw-bg-muted);border:1px solid var(--lw-border-soft);border-radius:var(--lw-radius-surface);padding:clamp(1.25rem,3vw,1.75rem) var(--lw-content-gutter);box-sizing:border-box}
.lw-band--alt>.lw-container{padding-inline:0}
.lw-site-frame .lw-footer{border-radius:0 0 var(--lw-frame-radius) var(--lw-frame-radius)}
@media(max-width:1023px){.lw-site-frame .lw-footer{border-radius:0}}
.lw-footer{border-top:1px solid rgba(153,246,228,.5);background:var(--lw-footer-bg);padding:clamp(.75rem,2vw,1rem) var(--lw-content-gutter);font-size:.8125rem;color:var(--lw-text-muted);width:100%;box-sizing:border-box;margin-top:auto;box-shadow:0 -2px 16px rgba(6,78,59,.05)}
.lw-footer-inner{width:100%;max-width:var(--lw-content-max);margin:0 auto;display:flex;flex-direction:column;gap:clamp(.5rem,1.25vw,.75rem);box-sizing:border-box}
.lw-footer-top{display:flex;flex-direction:column;align-items:center;gap:.375rem;width:100%;text-align:center}
.lw-footer-disclaimer{margin:0;padding:0;border:none;background:transparent}
.lw-footer-disclaimer-text{margin:0;font-size:.6875rem;line-height:1.45;color:var(--lw-text-body);text-align:center}
.lw-footer-security{margin:0;font-size:.6875rem;line-height:1.4;color:var(--lw-text-secondary);text-align:center}
.lw-footer-security strong{color:var(--lw-accent-dark);font-weight:700}
.lw-footer-security-link{margin-left:.25rem;color:var(--lw-accent);font-weight:600;text-decoration:none;white-space:nowrap}
.lw-footer-security-link:hover{text-decoration:underline;color:var(--lw-accent-hover)}
.lw-footer-main{display:flex;flex-direction:row;flex-wrap:wrap;justify-content:center;align-items:center;gap:1rem 1.5rem;width:100%;padding-top:.5rem;border-top:1px solid rgba(153,246,228,.45);text-align:center}
.lw-footer-brand{display:flex;flex-direction:column;align-items:center;width:auto;flex:0 1 auto}
.lw-footer-brand-head{display:flex;align-items:center;justify-content:center;gap:.625rem;text-align:left}
.lw-footer-brand-text{min-width:0;text-align:left}
.lw-footer-text{margin:0;font-weight:700;font-size:.875rem;color:var(--lw-accent-dark);line-height:1.3;letter-spacing:-.01em}
.lw-footer-logo{display:block;width:2.125rem;height:2.125rem;flex-shrink:0;object-fit:contain}
.lw-footer-secondary{margin:.125rem 0 0;font-size:.75rem;line-height:1.35;color:var(--lw-text-muted)}
.lw-footer-bottom{display:flex;flex-direction:column;align-items:center;gap:.375rem;width:100%;padding-top:.5rem;border-top:1px solid rgba(153,246,228,.45);text-align:center}
.lw-footer-aside{display:flex;justify-content:center;width:100%}
.lw-footer .lw-social-block--footer{width:auto;margin:0}
.lw-footer .lw-footer-social{justify-content:center;margin:0;gap:.375rem}
.lw-footer .lw-footer-social-link{padding:0;width:2rem;height:2rem;justify-content:center;border-radius:9999px;border:1px solid rgba(6,78,59,.16);background:var(--lw-bg-accent-muted);font-size:0}
.lw-footer .lw-footer-social-link:hover{transform:none;background:var(--lw-bg-accent-soft);border-color:var(--lw-border-accent)}
.lw-footer .lw-footer-social-label{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.lw-footer-copyright{margin:0;padding:0;border:none;font-size:.6875rem;line-height:1.4;color:var(--lw-text-faint);text-align:center;width:100%;max-width:36rem}
@media(max-width:767px){
.lw-footer-main{flex-direction:column;gap:.75rem}
}
@media(max-height:500px) and (orientation:landscape){
.lw-footer{padding:.5rem var(--lw-content-gutter)}
.lw-footer-inner{gap:.375rem}
.lw-footer-top{gap:.25rem}
.lw-footer-main{flex-direction:column;align-items:center;gap:.5rem;padding-top:.375rem}
.lw-footer-brand{max-width:none}
.lw-footer-bottom{flex-direction:column;align-items:center;justify-content:center;padding-top:.375rem;gap:.375rem;text-align:center}
.lw-footer-copyright{text-align:center;font-size:.625rem}
.lw-footer-aside{justify-content:center;width:100%}
}
@media(min-width:768px){
.lw-footer{padding:clamp(.75rem,1.5vw,1rem) var(--lw-content-gutter)}
.lw-footer-main{flex-direction:row;justify-content:center;align-items:center;gap:1rem 1.75rem}
.lw-footer-brand{max-width:none}
.lw-footer-bottom{flex-direction:column;align-items:center;justify-content:center;text-align:center;padding-top:.5rem}
.lw-footer-copyright{text-align:center;max-width:36rem;width:100%}
.lw-footer-aside{justify-content:center;width:100%}
.lw-footer .lw-footer-social{justify-content:center}
}
.lw-footer-social{display:flex;justify-content:center;align-items:center;gap:.625rem;margin-top:1rem;flex-wrap:wrap}
.lw-footer-social-link{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .75rem;border-radius:9999px;border:1px solid rgba(6,78,59,.2);background:var(--lw-bg-accent-muted);color:var(--lw-accent);font-size:.75rem;font-weight:600;text-decoration:none;transition:background .15s,border-color .15s}
.lw-footer-social-icon{width:1.125rem;height:1.125rem;flex-shrink:0}
.lw-footer-social-icon--page{width:1.25rem;height:1.25rem}
.lw-footer-social-link--page{gap:.5rem;padding:.55rem .9rem;font-size:.8125rem}
.lw-footer-social-label{line-height:1.2}
.lw-kegiatan-section{margin-top:2rem}
.lw-kegiatan-card{display:flex;flex-direction:column;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:1rem;overflow:hidden;box-shadow:var(--lw-shadow-sm)}
.lw-kegiatan-photo-wrap{margin:0;line-height:0;background:var(--lw-bg-subtle);border-bottom:1px solid var(--lw-border)}
.lw-kegiatan-photo{display:block;width:100%;height:auto;aspect-ratio:16/9;object-fit:cover}
.lw-kegiatan-card-inner{display:flex;flex-direction:column;gap:.35rem;height:100%;padding:1.125rem 1.25rem}
.lw-social-block--page{margin-top:0;text-align:center}
.lw-social-block-label{margin:0 0 .25rem;font-size:.875rem;font-weight:700;color:var(--lw-accent-text);text-transform:uppercase;letter-spacing:.04em}
.lw-social-block-lead{margin:0 0 1rem;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.5;max-width:32rem;margin-left:auto;margin-right:auto}
.lw-social-page-section{margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--lw-border)}
.lw-footer-social--page{justify-content:center}
.lw-kegiatan-date{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-text-muted)}
.lw-kegiatan-card-name{margin:0;font-weight:700;font-size:.9375rem;color:var(--lw-accent-text);line-height:1.35}
.lw-kegiatan-card-desc{margin:0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.5;flex-grow:1}
.lw-kegiatan-lokasi{margin:.25rem 0 0;font-size:.75rem;color:var(--lw-text-secondary)}
.lw-kegiatan-rt-badge{display:inline-block;width:fit-content;font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--lw-accent);background:var(--lw-bg-accent-soft);border:1px solid var(--lw-border-accent-strong);border-radius:9999px;padding:.2rem .55rem}
.lw-pengumuman-section{margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--lw-border)}
.lw-pengumuman-card{display:flex;flex-direction:column;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:1rem;padding:1.125rem 1.25rem;box-shadow:var(--lw-shadow-sm)}
.lw-pengumuman-card-inner{display:flex;flex-direction:column;gap:.35rem}
.lw-pengumuman-card-name{margin:0;font-weight:700;font-size:.9375rem;color:var(--lw-accent-text);line-height:1.35}
.lw-pengumuman-card-desc{margin:0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.5}
.lw-panel-publications-nav{display:flex;flex-wrap:wrap;gap:.5rem}
.lw-panel-btn--ghost{background:transparent;border:1px solid var(--lw-input-border);color:var(--lw-text-body)}
.lw-panel-btn--ghost.lw-panel-btn--active{background:var(--lw-bg-accent-soft);border-color:#6ee7b7;color:var(--lw-accent)}
.lw-panel-publication-preview{display:block;object-fit:cover}
.lw-panel-wa-recipient-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem;font-size:.8125rem}
.lw-panel-wa-recipient-list{max-height:16rem;overflow-y:auto;border:1px solid var(--lw-border-soft,var(--lw-border));border-radius:.75rem;padding:.75rem;display:grid;gap:.5rem;background:var(--lw-bg-card,#fff)}
.lw-panel-wa-recipient-item.is-disabled{opacity:.55;cursor:not-allowed}

/* Navbar */
.lw-site-frame .lw-nav{border-radius:var(--lw-frame-radius) var(--lw-frame-radius) 0 0}
@media(max-width:1023px){.lw-site-frame .lw-nav{border-radius:0}}
.lw-nav{position:sticky;top:0;z-index:50;background:var(--lw-nav-bg);border-bottom:1px solid var(--lw-nav-border);box-shadow:0 2px 12px rgba(6,78,59,.18);color:var(--lw-nav-text)}
.lw-nav-inner{width:100%;margin:0 auto;padding:.625rem var(--lw-content-gutter);display:grid;grid-template-columns:auto 1fr auto;grid-template-areas:"logo text menu";align-items:center;column-gap:.75rem;row-gap:.5rem;box-sizing:border-box}
.lw-nav-logo-wrap{grid-area:logo;flex-shrink:0;display:flex;align-items:center;justify-content:center;gap:.35rem;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.18);border-radius:.625rem;padding:.35rem .45rem;box-shadow:0 2px 8px rgba(0,0,0,.12);transition:transform .2s,box-shadow .2s,background .2s;text-decoration:none}
.lw-nav-portal-icon{display:block;height:2.5rem;width:2.5rem;flex-shrink:0;object-fit:contain}
.lw-nav-logo-wrap:hover{transform:translateY(-1px);background:rgba(255,255,255,.2);box-shadow:0 4px 12px rgba(0,0,0,.16)}
.lw-nav-logo{display:block;height:2rem;width:auto;max-width:4.75rem;object-fit:contain}
.lw-nav-text{grid-area:text;min-width:0;display:flex;flex-direction:column;justify-content:center;gap:.1rem;padding-right:.25rem}
.lw-nav-title{display:block;font-weight:700;font-size:.8125rem;line-height:1.25;color:var(--lw-nav-text);text-decoration:none;letter-spacing:-.01em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
.lw-nav-title:hover{color:var(--lw-nav-text-hover)}
.lw-nav-subtitle{display:block;font-size:.625rem;line-height:1.3;color:var(--lw-nav-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lw-nav-toggle{position:absolute;opacity:0;width:0;height:0;pointer-events:none}
.lw-nav-menu-btn{grid-area:menu;justify-self:end;display:flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.625rem;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.22);cursor:pointer;transition:background .2s,border-color .2s}
.lw-nav-menu-btn:hover{background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.32)}
.lw-nav-menu-icon{display:block;width:1.25rem;height:2px;background:#fff;border-radius:1px;box-shadow:0 -6px 0 #fff,0 6px 0 #fff}
.lw-nav-panel{grid-column:1/-1;display:none;flex-direction:column;padding-top:.25rem;border-top:1px solid var(--lw-nav-border)}
.lw-nav-toggle:checked~.lw-nav-panel{display:flex}
.lw-nav-toggle:checked~.lw-nav-menu-btn{background:rgba(255,255,255,.22);border-color:rgba(255,255,255,.35)}
.lw-nav-links{display:flex;flex-direction:column;gap:.25rem;padding:.5rem 0}
.lw-nav-link{display:block;border-radius:.5rem;padding:.625rem .875rem;font-size:.9375rem;color:var(--lw-nav-text-muted);text-decoration:none;transition:background .2s,color .2s}
.lw-nav-link:hover{background:var(--lw-nav-link-hover-bg);color:var(--lw-nav-text)}
.lw-nav-link-active{background:var(--lw-nav-link-active-bg);font-weight:600;color:var(--lw-nav-link-active-text)}
.lw-nav-link-btn{width:100%;text-align:left;background:transparent;border:none;cursor:pointer;font:inherit}
.lw-nav-logout-form{display:block}
.lw-nav-cta{display:block;text-align:center;border-radius:.625rem;padding:.625rem 1rem;font-size:.875rem;font-weight:600;background:var(--lw-nav-cta-bg);color:var(--lw-nav-cta-text);box-shadow:0 2px 8px rgba(0,0,0,.12);text-decoration:none;margin-top:.25rem;white-space:nowrap;transition:background .2s,box-shadow .2s,color .2s}
.lw-nav-cta:hover{background:var(--lw-bg-accent-soft);color:var(--lw-nav-cta-text)}
.lw-nav-cta-active{background:var(--lw-nav-cta-active-bg);color:var(--lw-nav-cta-text);box-shadow:0 0 0 2px rgba(255,255,255,.5),0 2px 8px rgba(0,0,0,.12)}
@media(max-width:1023px){
.lw-nav-title,.lw-nav-subtitle{white-space:normal;overflow:visible;text-overflow:unset;line-height:1.35}
.lw-nav-inner{row-gap:.625rem}
.lw-nav-toggle:checked~.lw-nav-panel{padding-top:.5rem;padding-bottom:.25rem}
.lw-nav-link{display:flex;align-items:center;min-height:2.75rem;padding:.75rem .875rem;line-height:1.4}
.lw-nav-cta{width:100%;margin-top:.375rem;min-height:2.75rem;display:flex;align-items:center;justify-content:center}
}
@media(min-width:480px){
.lw-nav-logo{height:2.25rem;max-width:5.5rem}
.lw-nav-title{font-size:.875rem}
.lw-nav-subtitle{font-size:.6875rem}
}
@media(min-width:640px){
.lw-nav-inner{padding:.75rem var(--lw-content-gutter);column-gap:1rem}
.lw-nav-logo{height:2.5rem;max-width:6.25rem}
.lw-nav-title{font-size:.9375rem}
.lw-nav-subtitle{font-size:.75rem}
}
@media(min-width:1024px){
.lw-nav-inner{grid-template-columns:auto 1fr;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem 1.25rem}
.lw-nav-logo-wrap,.lw-nav-text{grid-area:unset}
.lw-nav-menu-btn{display:none}
.lw-nav-panel{grid-column:unset;display:flex!important;flex:1 1 auto;justify-content:flex-end;padding-top:0;border-top:none;min-width:0}
.lw-nav-links{flex-direction:row;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:.375rem;padding:0}
.lw-nav-link{padding:.45rem .7rem;font-size:.8125rem;line-height:1.25;border-radius:.5rem}
.lw-nav-cta{margin-top:0;margin-left:.375rem;padding:.5rem 1rem;font-size:.8125rem;align-self:center;flex-shrink:0}
.lw-nav-title{white-space:normal}
.lw-nav-subtitle{white-space:normal}
}

/* Hero beranda */
.lw-home-hero{margin-bottom:2rem}
.lw-home-hero--bg.lw-hero{background-image:linear-gradient(105deg,rgba(255,255,255,.92) 0%,rgba(236,253,245,.88) 42%,rgba(4,120,87,.12) 70%,transparent 100%),var(--lw-home-hero-bg);background-size:cover;background-position:center;background-repeat:no-repeat;min-height:min(20rem,42vh)}
@media(min-width:768px){.lw-home-hero--bg.lw-hero{background-size:cover,auto 85%;background-position:center,right 1.25rem center;min-height:18rem}}
.lw-home-hero--bg .lw-hero-stats{background:rgba(255,255,255,.94);backdrop-filter:blur(10px)}
.lw-hero{border-radius:1.25rem;border:1px solid rgba(167,243,208,.95);background:var(--lw-hero-bg);box-shadow:var(--lw-shadow-md);overflow:hidden;padding:1.25rem 1rem}
@media(min-width:640px){.lw-hero{padding:1.75rem 1.5rem}}
.lw-hero-grid{display:flex;flex-direction:column;gap:1.5rem}
@media(min-width:1024px){.lw-hero-grid{flex-direction:row;align-items:stretch;gap:2rem}}
.lw-hero-content{flex:1;min-width:0}
.lw-hero-eyebrow{display:flex;align-items:center;gap:.4rem;margin:0 0 .5rem;font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--lw-accent)}
.lw-hero-eyebrow-dot{width:.5rem;height:.5rem;border-radius:9999px;background:#10b981;box-shadow:0 0 0 4px rgba(16,185,129,.35)}
.lw-hero-title{margin:0;font-size:1.875rem;font-weight:800;line-height:1.15;color:var(--lw-text-strong);letter-spacing:-.03em}
@media(min-width:640px){.lw-hero-title{font-size:2.25rem}}
.lw-hero-title-accent{color:var(--lw-accent);display:inline-block}
.lw-hero-lead{margin:.75rem 0 0;max-width:36rem;line-height:1.6;color:var(--lw-text-secondary);font-size:.9375rem}
.lw-hero-note{margin:.75rem 0 0;max-width:36rem;font-size:.8125rem;line-height:1.5;color:var(--lw-text-muted)}
.lw-home-hero .lw-services-hero-points{margin-top:1rem}
.lw-home-cta-row{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.25rem}
.lw-home-cta-row .lw-btn-primary,.lw-home-cta-row .lw-btn-secondary{padding:.625rem 1.25rem;font-size:.875rem;border-radius:.75rem}
.lw-hero-btn-main{padding:.6875rem 1.375rem;font-size:.875rem;border-radius:.75rem;font-weight:600}
.lw-hero-stats{flex-shrink:0;align-self:flex-start;width:100%;max-width:none;border-radius:1rem;background:var(--lw-bg-card-translucent);border:1px solid var(--lw-border-accent);padding:1.125rem}
@media(min-width:1024px){.lw-hero-stats{max-width:17.5rem}}
.lw-hero-stats-label{margin:0 0 .875rem;font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:var(--lw-text-muted)}
.lw-hero-stats-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.75rem .875rem}
.lw-hero-stat{border-radius:.75rem;padding:.625rem;background:var(--lw-bg-muted);border:1px solid var(--lw-border-accent);text-align:center}
.lw-hero-stat-value{display:block;font-size:1.5rem;font-weight:800;line-height:1;color:var(--lw-accent-dark)}
.lw-hero-stat-name{display:block;margin-top:.2rem;font-size:.6875rem;font-weight:500;color:var(--lw-text-muted);line-height:1.35}
.lw-hero-stats-foot{margin:.875rem 0 0;text-align:center;font-size:.6875rem;color:var(--lw-text-faint);line-height:1.4}
.lw-services-hero-points{margin:.75rem 0 0;padding:0;list-style:none;text-align:left;font-size:.8125rem;color:var(--lw-text-secondary);line-height:1.5;display:flex;flex-direction:column;gap:.5rem}
.lw-services-hero-points li{padding-left:0}
.lw-services-hero-points strong{color:var(--lw-accent-dark)}

/* Kartu layanan beranda */
.lw-section-tag{display:inline-flex;align-items:center;padding:.25rem .625rem;font-size:clamp(.6875rem,1.8vw,.75rem);font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--lw-accent);background:var(--lw-bg-accent-soft);border-radius:9999px;margin:0}
.lw-section-title{font-size:1.25rem;font-weight:800;color:var(--lw-text);letter-spacing:-.02em;margin:0}
.lw-section-desc{margin:.35rem 0 0;max-width:40rem;font-size:.875rem;line-height:1.55;color:var(--lw-text-muted)}
.lw-section-head-stack,.lw-home-section-head,.lw-profile-section-head{display:flex;flex-direction:column;align-items:flex-start;gap:.375rem}
.lw-section-head-stack .lw-section-tag,.lw-home-section-head .lw-section-tag,.lw-profile-section-head .lw-section-tag{margin:0}
.lw-section-head-stack .lw-section-title,.lw-home-section-head .lw-section-title,.lw-profile-section-head .lw-section-title{margin:0;line-height:1.3}
.lw-section-head-stack .lw-section-desc,.lw-home-section-head .lw-section-desc,.lw-profile-section-head .lw-section-desc{margin:0;max-width:min(40rem,100%)}
.lw-auth-hub-head,.lw-auth-hub-head--compact{display:flex;flex-direction:column;align-items:flex-start;gap:.375rem}
.lw-auth-hub-head .lw-section-tag,.lw-auth-hub-head--compact .lw-section-tag{margin:0}
.lw-auth-hub-head .lw-section-title,.lw-auth-hub-head--compact .lw-section-title{margin:0;line-height:1.3}
.lw-auth-hub-head .lw-auth-hub-lead,.lw-auth-hub-head--compact .lw-auth-hub-lead{margin:0}
.lw-home-services-intro{margin-bottom:1rem}
.lw-home-services-head{margin-bottom:1rem;display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:.75rem}
.lw-services-grid{display:grid;gap:var(--lw-card-gap);margin-bottom:.5rem;width:100%}
@media(min-width:640px){.lw-services-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1024px){.lw-services-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
.lw-service-card{display:flex;flex-direction:column;width:100%;min-width:0;box-sizing:border-box;text-decoration:none;color:inherit;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:1rem;padding:1.125rem 1.25rem;box-shadow:var(--lw-shadow-sm);transition:transform .18s ease,box-shadow .18s,border-color .18s;-webkit-tap-highlight-color:transparent}
.lw-service-card:hover{border-color:#6ee7b7;box-shadow:0 12px 32px rgba(6,78,59,.14);transform:translateY(-2px)}
.lw-service-card-inner{display:flex;flex-direction:column;gap:.35rem;height:100%}
.lw-service-card-name{font-weight:700;font-size:.9375rem;color:var(--lw-accent-text);line-height:1.35;margin:0}
.lw-service-card-desc{font-size:.8125rem;color:var(--lw-text-muted);line-height:1.5;margin:0;flex-grow:1}
.lw-service-card-arrow{margin-top:.5rem;font-weight:700;font-size:.75rem;color:var(--lw-accent);display:inline-flex;align-items:center;gap:.25rem}
.lw-home-foot-link{display:inline-flex;margin-top:.25rem;font-size:.8125rem;font-weight:600;color:var(--lw-accent);text-decoration:none}
.lw-home-foot-link:hover{text-decoration:underline}

/* Minimal panel RT (halaman pengurus) */
.lw-rt-filter-tabs{display:flex;gap:.5rem;border-bottom:1px solid var(--lw-border);padding-bottom:.5rem;margin-bottom:1rem}
.lw-rt-filter-tab{font-size:.8125rem;font-weight:600;padding:.35rem .75rem;border-radius:.5rem;color:var(--lw-text-secondary);text-decoration:none}
.lw-rt-filter-tab:hover{background:var(--lw-bg-accent-soft);color:var(--lw-accent-text)}
.lw-rt-filter-tab.is-active{background:var(--lw-accent);color:#fff}
.lw-btn-primary{display:inline-flex;align-items:center;justify-content:center;padding:.4375rem 1rem;border-radius:.5rem;background:var(--lw-accent);color:#fff;font-weight:600;text-decoration:none;border:none;cursor:pointer;font-size:.8125rem;transition:background .15s}
.lw-btn-primary:hover{background:var(--lw-accent-hover)}
.lw-btn-secondary{display:inline-flex;align-items:center;padding:.4375rem 1rem;border-radius:.5rem;border:2px solid var(--lw-input-border);color:var(--lw-text-body);text-decoration:none;font-weight:600;background:var(--lw-bg-card);font-size:.8125rem}
.lw-btn-sm{padding:.3125rem .75rem;font-size:.8125rem!important}
.lw-panel-link{color:var(--lw-accent);font-weight:600;text-decoration:none}
.lw-panel-link:hover{text-decoration:underline}
.lw-rt-page-header{display:flex;flex-wrap:wrap;justify-content:space-between;gap:1rem;margin-bottom:1rem}
.lw-rt-dash-eyebrow{font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--lw-accent);margin:0}
.lw-rt-page-title{margin:.25rem 0 0;font-size:1.375rem;font-weight:800;color:var(--lw-accent-dark)}
.lw-rt-page-lead{font-size:.875rem;color:var(--lw-text-muted);line-height:1.55;max-width:42rem}
.lw-rt-dash-section-title{font-size:1rem;font-weight:700;color:var(--lw-text);margin:0}
/* Halaman hub layanan (/layanan) ringkas */
.lw-services-catalog-lead{font-size:.875rem;color:var(--lw-text-muted);line-height:1.55;margin-top:.5rem}

/* Lompat bagian beranda/layanan */
.lw-services-jump{display:flex;flex-wrap:nowrap;gap:.5rem;margin-top:1rem;overflow-x:auto;-webkit-overflow-scrolling:touch;padding-bottom:.125rem}
.lw-services-jump-link{flex-shrink:0;display:inline-flex;align-items:center;padding:.45rem .85rem;font-size:.75rem;font-weight:700;color:var(--lw-accent-text);background:var(--lw-bg-card);border:1px solid var(--lw-border-accent-strong);border-radius:9999px;text-decoration:none;white-space:nowrap;box-shadow:0 2px 8px rgba(6,78,59,.06)}
.lw-services-jump-link:hover{background:var(--lw-bg-accent-soft);border-color:#34d399}

/* Section anchor (navbar sticky offset) */
.lw-services-section{scroll-margin-top:5.25rem;padding-bottom:2.5rem}
@media(min-width:768px){.lw-services-section{padding-bottom:3rem}}

/* Alur — grid langkah */
.lw-flow-grid{display:grid;gap:.875rem}
@media(min-width:640px){.lw-flow-grid{grid-template-columns:repeat(2,1fr);gap:1rem}}
@media(min-width:1024px){.lw-flow-grid{grid-template-columns:repeat(3,1fr);gap:1.125rem}}
.lw-flow-step{display:flex;gap:.75rem;align-items:flex-start;padding:1rem 1.125rem;border-radius:1rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);box-shadow:0 4px 14px rgba(15,23,42,.05);min-height:100%}
.lw-flow-step-num{flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;border-radius:.75rem;background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;font-size:.75rem;font-weight:800}
.lw-flow-step-body{min-width:0}
.lw-flow-step-title{margin:0 0 .35rem;font-size:.875rem;font-weight:700;color:var(--lw-text);line-height:1.3}
.lw-flow-step-desc{margin:0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.55}

/* Alur layanan — tab per jenis layanan (CSS-only) */
.lw-service-flow-tabs__root{display:flex;flex-direction:column;gap:1rem}
.lw-service-flow-tabs__input{position:absolute;opacity:0;pointer-events:none;width:0;height:0}
.lw-service-flow-tabs__bar{display:flex;gap:.5rem;overflow-x:auto;padding-bottom:.125rem;-webkit-overflow-scrolling:touch;scrollbar-width:thin}
.lw-service-flow-tabs__tab{flex-shrink:0;display:inline-flex;align-items:center;padding:.5rem 1rem;border-radius:9999px;border:1px solid var(--lw-border-soft);background:var(--lw-bg-muted);font-size:.8125rem;font-weight:600;color:var(--lw-text-muted);cursor:pointer;transition:background .15s,border-color .15s,color .15s;white-space:nowrap;line-height:1.3}
.lw-service-flow-tabs__tab:hover{border-color:var(--lw-border-accent);color:var(--lw-accent-text)}
.lw-service-flow-panel{display:none;scroll-margin-top:5.5rem}
.lw-service-flow-panel__intro{margin:0 0 1rem;font-size:.875rem;color:var(--lw-text-muted);line-height:1.55;max-width:48rem}
#flow-tab-surat:checked ~ .lw-service-flow-tabs__bar label[for="flow-tab-surat"],
#flow-tab-pendataan_ulang:checked ~ .lw-service-flow-tabs__bar label[for="flow-tab-pendataan_ulang"],
#flow-tab-pendataan_warga:checked ~ .lw-service-flow-tabs__bar label[for="flow-tab-pendataan_warga"]{border-color:var(--lw-border-accent-strong);background:var(--lw-bg-accent-soft);color:var(--lw-accent-text);box-shadow:0 2px 8px rgba(15,118,110,.12)}
#flow-tab-surat:checked ~ .lw-service-flow-panel[data-flow-key="surat"],
#flow-tab-pendataan_ulang:checked ~ .lw-service-flow-panel[data-flow-key="pendataan_ulang"],
#flow-tab-pendataan_warga:checked ~ .lw-service-flow-panel[data-flow-key="pendataan_warga"]{display:block}

/* Intro katalog + peringatan kelurahan */
.lw-services-admin-intro{display:flex;flex-direction:column;gap:.25rem;border-radius:1.25rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);box-shadow:0 8px 28px rgba(6,78,59,.08);padding:1.25rem 1.125rem}
@media(min-width:640px){.lw-services-admin-intro{padding:1.5rem 1.5rem}}
.lw-kelurahan-slot{margin:1rem 0 1.25rem}
.lw-kelurahan-notice{border-radius:.875rem;border:1px solid var(--lw-notice-border);background:var(--lw-notice-bg);padding:1rem 1.125rem;font-size:.8125rem;color:var(--lw-text-body);line-height:1.55;box-shadow:0 2px 10px rgba(14,165,233,.06)}
.lw-kelurahan-notice-title{margin:0 0 .375rem;font-weight:700;color:#0369a1;font-size:.875rem}
.lw-kelurahan-notice-text{margin:0}

/* Grid katalog (hingga 3 kolom lebar layar besar) */
.lw-catalog-grid{display:grid;gap:var(--lw-card-gap);width:100%}
@media(min-width:640px){.lw-catalog-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1280px){.lw-catalog-grid{grid-template-columns:repeat(3,1fr)}}
.lw-service-card-btn-wrap{margin-top:auto;padding-top:.5rem}
@media(max-width:639px){.lw-service-card .lw-service-card-arrow{display:block;text-align:center;width:100%;padding:.5rem 0;background:var(--lw-bg-accent-soft);border-radius:.5rem}}

.lw-persyaratan-umum{margin-bottom:1.5rem}
.lw-persyaratan-grid{display:grid;gap:var(--lw-card-gap);width:100%;margin-top:1.25rem}
@media(min-width:640px){.lw-persyaratan-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1280px){.lw-persyaratan-grid{grid-template-columns:repeat(3,1fr)}}
.lw-persyaratan-card{display:flex;flex-direction:column;gap:.5rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:1rem;padding:1.125rem 1.25rem;box-shadow:var(--lw-shadow-sm)}
.lw-persyaratan-card-title{font-weight:700;font-size:.9375rem;color:var(--lw-accent-text);line-height:1.35;margin:0}
.lw-persyaratan-card-list{margin:.25rem 0 0;padding-left:1.25rem;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.55}
.lw-persyaratan-card-list li{margin-bottom:.25rem}
.lw-persyaratan-card-fallback{font-size:.8125rem;color:var(--lw-text-muted);line-height:1.5;margin:0}
.lw-persyaratan-card-link{margin-top:auto;padding-top:.5rem;font-weight:700;font-size:.75rem;color:var(--lw-accent);text-decoration:none}
.lw-persyaratan-card-link:hover{text-decoration:underline}
.lw-persyaratan-cta{margin-top:1.75rem}

/* Hero halaman layanan satu kolom */
.lw-services-page-hero .lw-hero-content{max-width:42rem}
.lw-services-page-hero .lw-hero-grid{flex-direction:column}
@media(min-width:1024px){.lw-services-page-hero.lw-home-hero .lw-hero-grid{max-width:48rem}}

/* Kartu pendataan */
.lw-pendataan-card{border-radius:1.25rem;border:1px solid var(--lw-border);background:var(--lw-pendataan-card-bg);box-shadow:var(--lw-shadow-md);overflow:hidden}
.lw-pendataan-card-inner{padding:1.375rem 1.25rem 1.5rem;border:1px solid rgba(6,148,162,.35);border-radius:1.25rem;background:rgba(255,255,255,.72)}
@media(min-width:640px){.lw-pendataan-card-inner{padding:1.75rem 2rem}}
.lw-pendataan-tag{margin-bottom:.5rem}
.lw-pendataan-title{margin:0 0 .625rem;font-size:1.25rem;font-weight:800;color:#0c4a6e;letter-spacing:-.02em}
@media(min-width:640px){.lw-pendataan-title{font-size:1.375rem}}
.lw-pendataan-lead{margin:0;max-width:40rem;font-size:.875rem;color:var(--lw-text-secondary);line-height:1.6}
.lw-pendataan-actions{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.25rem}
.lw-pendataan-cta{padding:.625rem 1.25rem!important;font-size:.875rem!important}
.lw-pendataan-cta-lite{background:var(--lw-bg-card)!important}

/* Halaman Profil RT (/profil) */
.lw-profile-page{display:flex;flex-direction:column;gap:1.5rem;min-width:0}
.lw-profile-page .lw-profile-hero{margin:0;padding:0;border:none;background:transparent;box-shadow:none}
.lw-profile-hero--v2{background:var(--lw-hero-bg);border-bottom:1px solid var(--lw-border-soft)}
.lw-profile-hero--v2 .lw-profile-hero__inner{max-width:42rem;margin-inline:auto;padding:1.5rem 0 1.75rem;text-align:center}
.lw-profile-hero__eyebrow{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:.375rem;margin:0 0 .625rem;font-size:.8125rem;font-weight:600;line-height:1.4;color:var(--lw-accent-text)}
.lw-profile-hero__title{margin:0;font-size:clamp(1.5rem,4vw,2rem);font-weight:800;line-height:1.2;color:var(--lw-text-strong);letter-spacing:-.02em;text-align:center}
.lw-profile-hero__lead{margin:.625rem auto 0;max-width:40rem;font-size:.9375rem;line-height:1.6;color:var(--lw-text-muted);text-align:justify}
.lw-profile-page .lw-profile-board{display:flex;flex-direction:column;gap:1.75rem;margin-top:0;padding-bottom:2rem}
.lw-services-page .lw-profile-hero{margin:0;padding:0;border:none;background:transparent;box-shadow:none}
.lw-contact-page .lw-profile-hero,.lw-track-page .lw-profile-hero,.lw-auth-page-wrapper .lw-profile-hero,.lw-activities-page .lw-profile-hero,.lw-security-page .lw-profile-hero{margin:0;padding:0;border:none;background:transparent;box-shadow:none}
.lw-services-board,.lw-contact-board,.lw-track-board,.lw-auth-board,.lw-security-board{display:flex;flex-direction:column;gap:1.75rem;margin-top:0;padding-bottom:2rem}
.lw-track-page .lw-track-board{gap:clamp(.875rem,2vw,1.25rem);padding-bottom:1rem}
.lw-contact-page.lw-contact-split .lw-contact-board{gap:clamp(1.5rem,3vw,2.5rem);padding-bottom:clamp(1.5rem,3vw,2.5rem)}
.lw-contact-page,.lw-track-page,.lw-auth-page-wrapper,.lw-security-page{display:flex;flex-direction:column;gap:var(--lw-section-gap);min-width:0;width:100%}
.lw-security-page .lw-profile-hero--v2 .lw-profile-hero__inner{max-width:72rem}
.lw-security-panel{width:100%;box-sizing:border-box;border-radius:1rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);padding:1.25rem 1.125rem;box-shadow:var(--lw-shadow-sm)}
@media(min-width:640px){.lw-security-panel{padding:1.5rem 1.5rem}}
.lw-security-panel .lw-profile-section-head{margin-bottom:1rem}
.lw-security-subheading{margin:0 0 .625rem;font-size:.9375rem;font-weight:700;color:var(--lw-accent-dark);line-height:1.35}
.lw-security-list{margin:0 0 1.25rem;padding:0 0 0 1.25rem;display:flex;flex-direction:column;gap:.5rem;font-size:.875rem;line-height:1.55;color:var(--lw-text-secondary)}
.lw-security-list li::marker{color:var(--lw-accent)}
.lw-security-policy{margin:0;font-size:.8125rem;line-height:1.5;color:var(--lw-text-muted)}
.lw-contact-page .lw-profile-hero--v2 .lw-profile-hero__inner{max-width:72rem}
.lw-track-page .lw-profile-hero--v2 .lw-profile-hero__inner{max-width:72rem}
.lw-contact-page .lw-form-card{max-width:none}
.lw-contact-form-card{display:flex;flex-direction:column;gap:1rem;max-width:none}
.lw-contact-form-head{margin-bottom:0}
.lw-contact-form-card .lw-form-callout{margin:0}
.lw-contact-form.lw-form-stack>.lw-form-field,
.lw-contact-form .lw-form-grid--labeled>.lw-form-field{margin-bottom:0}
.lw-contact-form .lw-form-check{margin-top:.25rem}
@media(min-width:640px){.lw-contact-form .lw-form-check{padding-left:calc(var(--lw-form-label-col) + 1rem)}}
@media(min-width:768px){.lw-contact-form{--lw-form-label-col:14.5rem}}
.lw-auth-page-wrapper .lw-auth-split{margin:0}
.lw-contact-page .lw-profile-section-head.lw-home-section-head,.lw-track-page .lw-profile-section-head.lw-home-section-head,.lw-auth-page-wrapper .lw-profile-section-head.lw-home-section-head,.lw-security-page .lw-profile-section-head.lw-home-section-head{margin-bottom:0}
.lw-services-hub-section,.lw-services-catalog-section{display:flex;flex-direction:column;gap:1.25rem;scroll-margin-top:5.25rem}
.lw-services-page .lw-profile-section-head.lw-home-section-head{margin-bottom:0}
.lw-services-page .lw-home-process{margin-top:0}
.lw-profile-lurah-card{border-radius:1rem;border:1px solid var(--lw-border-accent);background:var(--lw-bg-card);box-shadow:var(--lw-shadow-sm);padding:1.375rem 1.25rem;transition:box-shadow .2s ease}
@media(min-width:640px){.lw-profile-lurah-card{padding:1.75rem 1.5rem}}
.lw-profile-lurah-card:hover{box-shadow:var(--lw-shadow-md)}
.lw-profile-lurah-card__body{display:flex;flex-direction:column;align-items:center;text-align:center;gap:1.25rem;margin-top:1.25rem}
.lw-profile-lurah-card__body--rt-style{max-width:28rem;margin-left:auto;margin-right:auto}
.lw-profile-lurah-card__header{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:.5rem;width:100%}
.lw-profile-lurah-card__body--rt-style .lw-profile-rt-card__photo{margin-bottom:0}
.lw-profile-lurah-card__photo{flex-shrink:0;width:7.5rem;height:7.5rem;border-radius:9999px;overflow:hidden;border:3px solid var(--lw-border-accent);background:var(--lw-bg-muted);box-shadow:0 4px 16px rgba(15,118,110,.12)}
.lw-profile-lurah-card__img{display:block;width:100%;height:100%;object-fit:cover;object-position:center top}
.lw-profile-lurah-card__placeholder{display:flex;align-items:center;justify-content:center;width:100%;height:100%;background:linear-gradient(145deg,var(--lw-bg-muted),var(--lw-bg-subtle));color:var(--lw-accent)}
.lw-profile-lurah-card__initial{font-size:2.25rem;font-weight:800;line-height:1}
.lw-profile-lurah-card__content{flex:1;min-width:0}
.lw-profile-lurah-card__name{margin:0;font-size:1.25rem;font-weight:800;color:var(--lw-text-strong);line-height:1.3;letter-spacing:-.02em}
.lw-profile-lurah-card__role{margin:.375rem 0 0;font-size:.875rem;color:var(--lw-text-muted);line-height:1.45}
.lw-profile-lurah-card__desc{margin:.875rem 0 0;font-size:.9375rem;line-height:1.6;color:var(--lw-text-body)}
.lw-profile-lurah-card__contacts{display:flex;flex-wrap:wrap;justify-content:center;gap:.5rem;margin:1rem 0 0;padding:0;list-style:none}
.lw-profile-lurah-card__contact{display:inline-flex;align-items:center;gap:.375rem;padding:.4375rem .75rem;border-radius:9999px;border:1px solid var(--lw-border-soft);background:var(--lw-bg-muted);font-size:.8125rem;font-weight:600;color:var(--lw-accent-text);text-decoration:none;line-height:1.3;transition:background .15s,border-color .15s}
.lw-profile-lurah-card__contact:hover{background:var(--lw-bg-accent-soft);border-color:var(--lw-border-accent)}
.lw-profile-lurah-card__contact--static{cursor:default;color:var(--lw-text-secondary)}
.lw-profile-rt-section{border-radius:1rem;border:1px solid var(--lw-border-accent);background:var(--lw-bg-card);box-shadow:var(--lw-shadow-sm);padding:1.375rem 1.25rem}
@media(min-width:640px){.lw-profile-rt-section{padding:1.75rem 1.5rem}}
.lw-profile-rt-grid{display:grid;grid-template-columns:1fr;gap:1rem;margin:1.25rem 0 0;padding:0;list-style:none}
@media(min-width:640px){.lw-profile-rt-grid{grid-template-columns:repeat(2,1fr);gap:1.125rem}}
@media(min-width:1024px){.lw-profile-rt-grid{grid-template-columns:repeat(3,1fr)}}
.lw-profile-rt-card{display:flex;flex-direction:column;align-items:center;text-align:center;height:100%;padding:1.25rem 1.125rem;border-radius:1rem;border:1px solid var(--lw-border-soft);background:var(--lw-bg-card);box-shadow:var(--lw-shadow-sm);transition:box-shadow .2s ease,border-color .2s ease,transform .2s ease}
.lw-profile-rt-card:hover{box-shadow:var(--lw-shadow-md);border-color:var(--lw-border-accent);transform:translateY(-2px)}
.lw-profile-rt-card.is-highlighted{border-color:var(--lw-border-accent-strong);box-shadow:0 0 0 3px rgba(94,234,212,.35),var(--lw-shadow-md)}
.lw-profile-rt-card__header{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:.5rem;margin-bottom:1rem;width:100%}
.lw-profile-rt-card__photo{width:5rem;height:5rem;border-radius:9999px;overflow:hidden;border:2px solid var(--lw-border-accent);background:var(--lw-bg-muted);margin-bottom:.875rem}
.lw-profile-rt-card__img{display:block;width:100%;height:100%;object-fit:cover}
.lw-profile-rt-card__placeholder{display:flex;align-items:center;justify-content:center;width:100%;height:100%;background:linear-gradient(145deg,var(--lw-bg-muted),var(--lw-bg-subtle));color:var(--lw-accent);font-size:1.5rem;font-weight:800}
.lw-profile-rt-card__body{flex:1;min-width:0;width:100%}
.lw-profile-rt-card__name{margin:0;font-size:1rem;font-weight:700;color:var(--lw-text-strong);line-height:1.35}
.lw-profile-rt-card__role{margin:.25rem 0 0;font-size:.8125rem;color:var(--lw-text-muted)}
.lw-profile-rt-card__stats{display:inline-flex;align-items:center;justify-content:center;gap:.375rem;margin:.75rem 0 0;font-size:.8125rem;color:var(--lw-text-secondary);line-height:1.4}
.lw-profile-rt-card__stats svg{flex-shrink:0;color:var(--lw-accent);opacity:.85}
.lw-profile-rt-card__cta{margin-top:1rem;width:100%;padding:.5625rem 1rem;font-size:.8125rem;border-radius:.625rem;text-decoration:none}
.lw-profile-wilayah{border-radius:1rem;border:1px solid var(--lw-border-soft);background:var(--lw-bg-muted);padding:1.25rem 1.125rem}
@media(min-width:640px){.lw-profile-wilayah{padding:1.375rem 1.5rem}}
.lw-profile-wilayah__inner{display:flex;gap:1rem;align-items:flex-start}
.lw-profile-wilayah__icon{flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:9999px;background:var(--lw-bg-accent-soft);color:var(--lw-accent)}
.lw-profile-wilayah__content{flex:1;min-width:0}
.lw-profile-wilayah__title{margin:0;font-size:1rem;font-weight:700;color:var(--lw-text-strong)}
.lw-profile-wilayah__list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.625rem 1.25rem;margin:1rem 0 0}
@media(min-width:640px){.lw-profile-wilayah__list{grid-template-columns:repeat(4,minmax(0,1fr))}}
.lw-profile-wilayah__list div{margin:0}
.lw-profile-wilayah__list dt{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--lw-text-faint);margin:0}
.lw-profile-wilayah__list dd{margin:.2rem 0 0;font-size:.875rem;font-weight:500;color:var(--lw-text-body);line-height:1.4}
.lw-profile-wilayah__desc{margin:1rem 0 0;font-size:.875rem;line-height:1.55;color:var(--lw-text-muted)}
.lw-profile-wilayah__map-note{margin:.75rem 0 0;padding:.75rem .875rem;border-radius:.625rem;border:1px dashed var(--lw-border-accent);background:var(--lw-bg-card);font-size:.8125rem;color:var(--lw-text-faint);line-height:1.45}
.lw-profile-back{margin:0 0 .5rem}
.lw-profile-back a{font-size:.875rem;font-weight:600;color:var(--lw-accent);text-decoration:none}
.lw-profile-back a:hover{text-decoration:underline}
.lw-profile-rt-show-card{border-radius:1rem;border:1px solid var(--lw-border-accent);background:var(--lw-bg-card);box-shadow:var(--lw-shadow-sm);padding:1.375rem 1.25rem}
@media(min-width:640px){.lw-profile-rt-show-card{padding:1.75rem 1.5rem}}
.lw-profile-rt-show-card__header{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;margin-bottom:1.25rem}
.lw-profile-rt-show-card__body{display:flex;flex-direction:column;align-items:center;text-align:center;gap:1.25rem}
@media(min-width:640px){.lw-profile-rt-show-card__body{flex-direction:row;align-items:flex-start;text-align:left;gap:1.75rem}}
.lw-profile-rt-show-card__photo{flex-shrink:0;width:7.5rem;height:7.5rem;border-radius:9999px;overflow:hidden;border:3px solid var(--lw-border-accent);background:var(--lw-bg-muted);box-shadow:0 4px 16px rgba(15,118,110,.12)}
.lw-profile-rt-show-card__img{display:block;width:100%;height:100%;object-fit:cover}
.lw-profile-rt-show-card__placeholder{display:flex;align-items:center;justify-content:center;width:100%;height:100%;background:linear-gradient(145deg,var(--lw-bg-muted),var(--lw-bg-subtle));color:var(--lw-accent);font-size:2.25rem;font-weight:800}
.lw-profile-rt-show-card__content{flex:1;min-width:0}
.lw-profile-rt-show-card__name{margin:0;font-size:1.375rem;font-weight:800;color:var(--lw-text-strong);line-height:1.3;letter-spacing:-.02em}
.lw-profile-rt-show-card__role{margin:.375rem 0 0;font-size:.875rem;color:var(--lw-text-muted);line-height:1.45}
.lw-profile-rt-show-card__staff{margin:1rem 0 0;padding:0;list-style:none;display:grid;gap:.625rem}
.lw-profile-rt-show-card__staff li{display:flex;flex-wrap:wrap;align-items:baseline;gap:.375rem .5rem;font-size:.875rem;line-height:1.45}
.lw-profile-rt-show-card__staff-role{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--lw-text-faint)}
.lw-profile-rt-show-card__staff-name{font-weight:600;color:var(--lw-text-body)}
.lw-profile-rt-show-card__staff-contact{font-size:.8125rem;font-weight:600;color:var(--lw-accent);text-decoration:none}
.lw-profile-rt-show-card__staff-contact:hover{text-decoration:underline}
.lw-profile-rt-show-card__stats{display:inline-flex;align-items:center;gap:.375rem;margin:1rem 0 0;font-size:.875rem;color:var(--lw-text-secondary);line-height:1.4}
.lw-profile-rt-show-card__stats svg{flex-shrink:0;color:var(--lw-accent);opacity:.85}
.lw-profile-rt-show-card__rw{margin:.75rem 0 0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.45}
.lw-profile-rt-show-card__contacts{display:flex;flex-wrap:wrap;justify-content:center;gap:.5rem;margin:1rem 0 0;padding:0;list-style:none}
@media(min-width:640px){.lw-profile-rt-show-card__contacts{justify-content:flex-start}}
.lw-profile-rt-show-card__contact{display:inline-flex;align-items:center;gap:.375rem;padding:.4375rem .75rem;border-radius:9999px;border:1px solid var(--lw-border-soft);background:var(--lw-bg-muted);font-size:.8125rem;font-weight:600;color:var(--lw-accent-text);text-decoration:none;line-height:1.3;transition:background .15s,border-color .15s}
.lw-profile-rt-show-card__contact:hover{background:var(--lw-bg-accent-soft);border-color:var(--lw-border-accent)}
.lw-profile-rt-show-card__contact--static{cursor:default;color:var(--lw-text-secondary)}
.lw-profile-page .lw-profile-hero--plain .lw-home-hero-v3-shell{margin:0;min-height:0;background-image:none;background:var(--lw-hero-bg);padding:1.25rem 1.125rem}
@media(min-width:640px){.lw-profile-page .lw-profile-hero--plain .lw-home-hero-v3-shell{padding:1.5rem 1.375rem}}
.lw-profile-page .lw-profile-hero--plain .lw-home-hero-v2-content{max-width:42rem}
.lw-profile-page .lw-profile-hero--plain .lw-hero-eyebrow{flex-wrap:wrap;margin:0 0 .625rem;text-transform:none;letter-spacing:normal;font-size:.8125rem;font-weight:600;line-height:1.4;color:var(--lw-accent-text)}
.lw-profile-page .lw-profile-hero--plain .lw-home-hero-v2-title{margin:0;line-height:1.25}
.lw-profile-page .lw-profile-hero--plain .lw-home-hero-v2-lead{margin:.625rem 0 0;max-width:42rem;line-height:1.6}
.lw-profile-page .lw-home-hero-v3-title-accent{margin-left:.2em}
.lw-profile-eyebrow-sep{margin:0 .125rem;opacity:.5;font-weight:400}
.lw-profile-page .lw-profile-section-head.lw-home-section-head{margin-bottom:1.25rem}
.lw-profile-board .lw-section-tag{text-transform:none;letter-spacing:normal;font-size:.75rem;font-weight:600;padding:.3125rem .625rem}
.lw-profile-detail-vision dt,.lw-profile-meta-item-body dt{text-transform:none;letter-spacing:normal;font-size:.6875rem;font-weight:700}
.lw-profile-section-head{margin-bottom:1rem}
.lw-profile-section-lead{margin:.5rem 0 0;max-width:40rem;font-size:.9375rem;line-height:1.55;color:var(--lw-text-muted)}
.lw-profile-board{margin-top:.25rem}
.lw-profile-card-stack{display:flex;flex-direction:column;gap:.875rem;margin:0;padding:0;list-style:none}
.lw-profile-card-mini{border-radius:1rem;border:1px solid var(--lw-border);background:var(--lw-profile-card-bg);box-shadow:var(--lw-shadow-sm);padding:1rem 1.125rem;text-align:left}
.lw-profile-card-mini-top{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.75rem}
.lw-profile-rt-chip{display:inline-flex;align-items:center;padding:.375rem .75rem;font-size:.875rem;font-weight:800;color:#fff;background:linear-gradient(135deg,var(--lw-accent-dark),var(--lw-accent-bright));border-radius:.625rem;letter-spacing:-.02em}
.lw-profile-rt-chip--table{font-size:.8125rem;padding:.3125rem .65rem;border-radius:.5rem}
.lw-profile-rw-chip{font-size:.75rem;font-weight:600;color:var(--lw-text-muted);text-transform:uppercase;letter-spacing:.04em}
.lw-profile-card-mini-dl{margin:0;display:grid;gap:.75rem}
.lw-profile-card-mini-dl div{margin:0}
.lw-profile-card-mini-dl dt{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-text-faint);margin:0}
.lw-profile-card-mini-dl dd{font-size:.875rem;font-weight:500;color:var(--lw-text);margin:.25rem 0 0;line-height:1.45}
.lw-profile-phone-link{color:var(--lw-accent);font-weight:600;text-decoration:none}
.lw-profile-phone-link:hover{text-decoration:underline}
.lw-profile-table-shell{border-radius:inherit}
.lw-profile-table-scroll{border-radius:.875rem;border:1px solid var(--lw-border);overflow:hidden;background:var(--lw-bg-card)}
.lw-profile-table{width:100%;border-collapse:collapse;font-size:.8125rem}
.lw-profile-table thead{background:linear-gradient(180deg,var(--lw-accent-dark) 0%,var(--lw-accent) 100%);color:#fff}
.lw-profile-table th{font-weight:700;text-align:left;padding:.875rem 1rem;letter-spacing:.02em;text-transform:none;font-size:.75rem}
.lw-profile-table td{padding:.8125rem 1rem;border-bottom:1px solid var(--lw-border-soft);color:var(--lw-text-body);vertical-align:top}
.lw-profile-table tbody tr:nth-child(even){background:rgba(248,250,252,.92)}
.lw-profile-table tbody tr:hover{background:var(--lw-table-hover)}
.lw-profile-table tbody tr:last-child td{border-bottom:none}
.lw-profile-person{font-weight:500;color:var(--lw-text)}
.lw-profile-caption{margin:1rem 0 0;font-size:.75rem;line-height:1.45;color:var(--lw-text-faint);text-align:center}
.lw-profile-aside-note{margin-top:1.75rem;padding:1rem 1.125rem;border-radius:.875rem;border:1px dashed var(--lw-border-accent);background:var(--lw-bg-accent-muted);font-size:.8125rem;color:var(--lw-text-secondary);line-height:1.55}
.lw-profile-aside-note-title{margin:0 0 .375rem;font-weight:700;color:var(--lw-accent-text)}
.lw-profile-aside-note-text{margin:0}
.lw-profile-aside-note-text a{color:var(--lw-accent);font-weight:700;text-decoration:none}
.lw-profile-aside-note-text a:hover{text-decoration:underline}
.lw-profile-empty{border:none;background:transparent;box-shadow:none}
.lw-profile-empty-lead{font-size:.875rem;line-height:1.6;max-width:40rem}
.lw-profile-detail-grid{display:grid;gap:1.5rem;align-items:start}
@media(min-width:768px){.lw-profile-detail-grid{grid-template-columns:minmax(200px,280px) 1fr;gap:2rem}}
.lw-profile-detail-grid--compact{display:flex;flex-direction:row;align-items:flex-start;gap:1rem}
.lw-profile-detail-grid--compact .lw-profile-detail-photo-wrap{flex-shrink:0;width:5.5rem}
.lw-profile-detail-grid--compact .lw-profile-detail-photo-wrap--lurah,
.lw-profile-detail-grid--compact .lw-profile-detail-photo-wrap--rt{border-radius:9999px;overflow:hidden}
.lw-profile-detail-grid--compact .lw-profile-detail-photo{width:5.5rem;height:5.5rem;border-radius:9999px;aspect-ratio:1;object-fit:cover}
.lw-profile-detail-grid--compact .lw-profile-detail-photo--lurah{width:5.5rem;height:5.5rem;border-radius:9999px;aspect-ratio:1;object-fit:cover;object-position:center top}
.lw-profile-detail-grid--compact .lw-profile-photo-placeholder--compact{width:5.5rem;height:5.5rem;min-height:0;aspect-ratio:1;border-radius:9999px;padding:.75rem}
.lw-profile-detail-grid--compact .lw-profile-photo-placeholder--compact.lw-profile-photo-placeholder--lurah{width:5.5rem;height:5.5rem;border-radius:9999px}
.lw-profile-detail-grid--compact .lw-profile-photo-placeholder--compact .lw-profile-photo-placeholder-icon{width:2rem;height:2rem}
.lw-profile-detail-grid--compact .lw-profile-photo-placeholder--compact .lw-profile-photo-placeholder-initial{font-size:1.5rem}
.lw-profile-detail-grid--compact .lw-profile-photo-placeholder--compact .lw-profile-photo-placeholder-label{display:none}
.lw-profile-detail-grid--compact .lw-profile-detail-chip{font-size:.625rem;padding:.25rem .5rem;left:.5rem;bottom:.5rem}
.lw-profile-detail-body--fill{flex:1;min-width:0}
.lw-profile-meta-grid{margin:.75rem 0 0;display:grid;gap:.625rem;grid-template-columns:1fr}
@media(min-width:480px){.lw-profile-meta-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}}
.lw-profile-meta-item{display:flex;align-items:flex-start;gap:.75rem;margin:0;padding:.75rem .875rem;border-radius:.75rem;border:1px solid var(--lw-border-soft);background:var(--lw-bg-muted)}
.lw-profile-meta-icon{flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;border-radius:9999px;background:var(--lw-bg-accent-soft);color:var(--lw-accent)}
.lw-profile-meta-icon--inline{width:1.75rem;height:1.75rem;margin-right:.375rem;vertical-align:middle}
.lw-profile-meta-item-body{flex:1;min-width:0}
.lw-profile-meta-item-body dt{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--lw-text-faint);margin:0}
.lw-profile-meta-item-body dd{margin:.2rem 0 0;font-size:.875rem;color:var(--lw-text-body);line-height:1.45}
.lw-profile-vision-text{display:block;font-size:.9375rem;line-height:1.6;color:var(--lw-text-body);white-space:pre-line}
.lw-profile-vision-list{margin:.25rem 0 0;padding-left:1.25rem;font-size:.9375rem;line-height:1.6;color:var(--lw-text-body)}
.lw-profile-vision-list li+li{margin-top:.35rem}
.lw-profile-map-placeholder{margin-top:.75rem;padding:1.25rem 1rem;border-radius:.75rem;border:1px dashed var(--lw-border-accent);background:var(--lw-bg-card);text-align:center}
.lw-profile-map-placeholder-text{font-size:.8125rem;line-height:1.55;color:var(--lw-text-muted)}
@media(min-width:768px){
.lw-profile-page .lw-profile-detail-grid--compact{display:grid;grid-template-columns:auto 1fr;gap:1.25rem 2rem;align-items:start}
.lw-profile-page .lw-profile-detail-grid--compact .lw-profile-detail-photo-wrap--lurah,
.lw-profile-page .lw-profile-detail-grid--compact .lw-profile-detail-photo-wrap--rt{width:6.5rem}
.lw-profile-page .lw-profile-detail-grid--compact .lw-profile-detail-photo--lurah,
.lw-profile-page .lw-profile-detail-grid--compact .lw-profile-detail-photo{width:6.5rem;height:6.5rem}
.lw-profile-page .lw-profile-detail-grid--compact .lw-profile-photo-placeholder--compact.lw-profile-photo-placeholder--lurah,
.lw-profile-page .lw-profile-detail-grid--compact .lw-profile-photo-placeholder--compact.lw-profile-photo-placeholder--rt{width:6.5rem;height:6.5rem}
.lw-profile-page .lw-profile-detail-grid--compact .lw-profile-detail-body--fill{min-width:0}
}
.lw-public-section-stack{display:flex;flex-direction:column;gap:var(--lw-section-gap);width:100%;min-width:0}
.lw-services-page,.lw-profile-page,.lw-kegiatan-page{display:flex;flex-direction:column;gap:var(--lw-section-gap);min-width:0;width:100%}
.lw-track-page{margin-left:auto;margin-right:auto}
.lw-track-card{padding:1.5rem 1.25rem;border-radius:1rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);box-shadow:var(--lw-shadow-sm)}
@media(min-width:640px){.lw-track-card{padding:1.75rem 1.5rem}}
.lw-track-header{text-align:center;margin-bottom:1.25rem}
.lw-track-label{display:block;text-align:center;font-size:.875rem;font-weight:600;color:var(--lw-text-secondary);margin-bottom:.35rem}
.lw-track-field{margin-bottom:0}
.lw-track-foot{margin-top:1.5rem;text-align:center;font-size:.875rem;color:var(--lw-text-muted);line-height:1.5}
.lw-track-status-header{text-align:center;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid var(--lw-border-soft)}
.lw-track-status-number{margin:0;font-size:1.125rem;font-weight:800;font-family:ui-monospace,monospace;color:var(--lw-accent-dark);word-break:break-all}
.lw-track-status-dl{margin:0;display:grid;gap:.75rem}
.lw-track-status-dl div{text-align:center}
.lw-track-status-dl dt{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-text-faint);margin:0}
.lw-track-status-dl dd{margin:.25rem 0 0;font-size:.9375rem;color:var(--lw-text-body);line-height:1.45}
.lw-track-progress-title{font-size:1rem;margin:1.5rem 0 0}
.lw-track-back{display:block;text-align:center;margin-bottom:1rem;font-size:.875rem}
.lw-profile-detail-photo-wrap{position:relative;border-radius:1.25rem;overflow:hidden;border:1px solid var(--lw-border);background:var(--lw-bg-card);box-shadow:0 8px 28px rgba(6,78,59,.1)}
.lw-profile-detail-photo{display:block;width:100%;height:auto;aspect-ratio:1;object-fit:cover}
.lw-profile-photo-placeholder{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;width:100%;aspect-ratio:1;min-height:200px;padding:1.5rem;text-align:center}
.lw-profile-photo-placeholder--rt{background:linear-gradient(145deg,var(--lw-bg-muted),var(--lw-bg-subtle));color:var(--lw-accent)}
.lw-profile-photo-placeholder--lurah{background:linear-gradient(145deg,var(--lw-bg-muted),var(--lw-bg-subtle));color:var(--lw-accent)}
.lw-profile-photo-placeholder-icon{opacity:.45;flex-shrink:0}
.lw-profile-photo-placeholder-initial{font-size:2.5rem;font-weight:800;line-height:1;opacity:.35}
.lw-profile-photo-placeholder-label{font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;opacity:.55}
.lw-services-admin-intro .lw-profile-detail{background:var(--lw-bg-muted);border:1px solid var(--lw-border-soft);border-radius:1rem;padding:1.25rem 1.125rem}
@media(min-width:640px){.lw-services-admin-intro .lw-profile-detail{padding:1.5rem 1.375rem}}
.lw-profile-rt-tab-badge{font-size:.625rem;font-weight:700;padding:.15rem .45rem;border-radius:9999px;background:rgba(255,255,255,.25);color:#fff;line-height:1.2}
.lw-profile-staff-section{margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--lw-border-soft)}
.lw-profile-rt-staff-note{margin:0 0 .75rem;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.45}
.lw-profile-staff-list{margin:0;padding:0;list-style:none}
.lw-profile-staff-list li+li{margin-top:.5rem}
.lw-profile-staff-name{display:block;font-weight:600;color:var(--lw-text)}
.lw-profile-staff-meta{display:block;font-size:.8125rem;color:var(--lw-text-muted);margin-top:.15rem}
.lw-profile-staff-empty{color:var(--lw-text-faint);font-style:italic}
.lw-profile-detail-brief{margin:.75rem 0 0;padding:0;list-style:none;font-size:.875rem;color:var(--lw-text-body);line-height:1.5}
.lw-profile-detail-brief li+li{margin-top:.35rem}
.lw-profile-detail-brief strong{color:var(--lw-text-secondary);font-weight:600}
.lw-profile-detail-more{margin-top:.5rem}
.lw-profile-detail-more-link{display:inline-flex;align-items:center;gap:.35rem;padding:0;font-size:.875rem;font-weight:600;color:var(--lw-accent);background:none;border:none;cursor:pointer;list-style:none;text-decoration:none}
.lw-profile-detail-more--lurah .lw-profile-detail-more-link{color:var(--lw-accent)}
.lw-profile-detail-more-link:hover{text-decoration:underline}
.lw-profile-detail-more-link::-webkit-details-marker{display:none}
.lw-profile-detail-more .when-open[hidden],
.lw-profile-detail-more:not([open]) .when-open{display:none!important}
.lw-profile-detail-more[open] .when-closed,
.lw-profile-detail-more[open] .when-closed:not([hidden]){display:none!important}
.lw-profile-detail-more[open] .when-open[hidden]{display:none!important}
.lw-profile-detail-more[open] .when-open:not([hidden]){display:inline!important}
.lw-profile-detail-more:not([open]) .when-closed[hidden]{display:none!important}
.lw-profile-detail-more:not([open]) .when-closed:not([hidden]){display:inline!important}
.lw-profile-detail-more-chevron{display:inline-block;width:.5rem;height:.5rem;border-right:2px solid currentColor;border-bottom:2px solid currentColor;transform:rotate(45deg);margin-top:-.2rem;transition:transform .2s}
.lw-profile-detail-more[open] .lw-profile-detail-more-chevron{transform:rotate(-135deg);margin-top:.15rem}
.lw-profile-detail-more-inner{margin-top:.75rem;padding-top:1rem;border-top:1px solid var(--lw-border);scroll-margin-top:5rem}
.lw-profile-detail-more--lurah .lw-profile-detail-more-inner{border-top-color:var(--lw-border-accent)}
.lw-profile-detail-chip{position:absolute;left:1rem;bottom:1rem;box-shadow:0 4px 12px rgba(6,78,59,.25)}
.lw-profile-detail-title{margin:0;font-size:1.25rem;font-weight:800;color:var(--lw-accent-dark);line-height:1.25}
.lw-profile-detail-subtitle{margin:.25rem 0 .75rem;font-size:.8125rem;line-height:1.45;color:var(--lw-text-muted)}
.lw-profile-detail-subtitle-sep{margin:0 .25rem;opacity:.65}
.lw-profile-detail-rw{display:block;font-size:.875rem;font-weight:600;color:var(--lw-text-muted);margin-top:.25rem}
.lw-profile-detail-meta{margin:0 0 1rem;font-size:.8125rem;color:var(--lw-text-muted)}
.lw-profile-detail-meta--compact{margin:0 0 .5rem}
.lw-profile-detail-dl{margin:0;display:grid;gap:.75rem}
.lw-profile-detail-dl dt{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-text-faint)}
.lw-profile-detail-dl dd{margin:.2rem 0 0;font-size:.9375rem;color:var(--lw-text);line-height:1.45}
.lw-profile-detail-vision{margin-top:.75rem;padding:1rem 1.125rem;border-radius:1rem;background:var(--lw-bg-accent-muted);border:1px solid var(--lw-border-accent);box-shadow:var(--lw-shadow-sm);font-size:.8125rem;color:var(--lw-text-secondary);line-height:1.55;display:grid;gap:.75rem}
.lw-profile-page .lw-profile-detail{padding:.25rem 0}
.lw-profile-detail-vision--summary{margin-top:.75rem}
.lw-profile-detail-body>.lw-profile-detail-brief{margin-top:.75rem}
.lw-profile-detail-vision div{margin:0}
.lw-profile-detail-vision dt{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-accent-text);margin:0}
.lw-profile-detail-vision dd{margin:.25rem 0 0;font-size:.9375rem;color:var(--lw-text-body);line-height:1.55}
.lw-profile-detail-vision p{margin:0 0 .5rem}
.lw-profile-detail-vision p:last-child{margin-bottom:0}
.lw-profile-page .lw-profile-lurah-card,.lw-profile-page .lw-profile-rt-section{margin-bottom:0}
.lw-profile-board .lw-services-admin-intro{margin-top:0}
.lw-profile-board .lw-services-admin-intro + .lw-services-admin-intro{margin-top:1.25rem}

/* Form publik — design system */
.lw-form-card{width:100%;max-width:48rem;margin-inline:auto;box-sizing:border-box;border-radius:1rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);padding:1.25rem 1.125rem;box-shadow:var(--lw-shadow-sm)}
@media(min-width:640px){.lw-form-card{padding:1.5rem 1.5rem}}
.lw-form-stack{display:flex;flex-direction:column;gap:1.25rem}
.lw-form-section{padding-top:1.25rem;margin-top:.25rem;border-top:1px solid var(--lw-border-soft)}
.lw-form-section:first-child,.lw-form-section--first{border-top:none;padding-top:0;margin-top:0}
.lw-form-legend,.lw-form-section-title{margin:0 0 .75rem;font-size:.875rem;font-weight:700;color:var(--lw-accent-text);line-height:1.35}
.lw-form-fieldset{border:none;padding:0;margin:0}
.lw-form-fieldset .lw-form-options{display:flex;flex-direction:column;gap:.625rem}
@media(min-width:480px){.lw-form-fieldset .lw-form-options--row{flex-direction:row;flex-wrap:wrap;gap:1rem 1.25rem}}
.lw-form-option{display:flex;align-items:flex-start;gap:.5rem;font-size:.875rem;color:var(--lw-text-body);line-height:1.45;cursor:pointer}
.lw-form-option input{flex-shrink:0;margin-top:.2rem;accent-color:var(--lw-accent)}
.lw-form-grid{display:grid;gap:1rem}
@media(min-width:640px){.lw-form-grid--2{grid-template-columns:repeat(2,1fr)}}
.lw-form--labeled .lw-household-recap-fields{grid-column:1/-1;width:100%}
.lw-form--labeled .lw-household-recap-fields>.lw-form-field{display:flex;flex-direction:column;gap:.25rem;margin-bottom:0}
.lw-form--labeled .lw-household-recap-fields>.lw-form-field>.lw-form-label{padding-top:0;max-width:none}
.lw-form--labeled .lw-household-recap-fields>.lw-form-field>.lw-form-hint,
.lw-form--labeled .lw-household-recap-fields>.lw-form-field>.lw-form-error{grid-column:auto}
.lw-form--labeled .lw-form-field--check-row{grid-column:1/-1;display:flex;flex-direction:column;gap:.35rem;margin-bottom:0}
.lw-form--labeled .lw-form-field--check-row .lw-form-check{max-width:100%}
.lw-form--labeled .lw-form-field--check-row input[disabled]{cursor:default;opacity:1}
.lw-form-field{display:flex;flex-direction:column;gap:.35rem}
.lw-form-field--span2{grid-column:1/-1}
.lw-form-grid--full{grid-column:1/-1}
.lw-form-label{font-size:.8125rem;font-weight:600;color:var(--lw-text-secondary);line-height:1.35}
.lw-form-label-required{color:#dc2626;font-weight:700}
.lw-form-input,.lw-form-select,.lw-form-textarea{width:100%;border:1px solid var(--lw-input-border);border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;line-height:1.4;color:var(--lw-text-body);background:var(--lw-input-bg);transition:border-color .15s,box-shadow .15s}
.lw-form-input:focus,.lw-form-select:focus,.lw-form-textarea:focus{outline:none;border-color:var(--lw-accent);box-shadow:0 0 0 3px rgba(15,118,110,.18)}
.lw-form-input:disabled{background:var(--lw-input-disabled);color:var(--lw-text-faint);cursor:not-allowed}
.lw-form-textarea{resize:vertical;min-height:4.5rem}
.lw-form-hint{margin:0;font-size:.75rem;color:var(--lw-text-muted);line-height:1.45}
.lw-form-hint--warn{color:#b45309}
.lw-form-error{margin:.25rem 0 0;font-size:.8125rem;color:#dc2626;line-height:1.4}
.lw-form-check{display:flex;align-items:flex-start;gap:.5rem;font-size:.8125rem;color:var(--lw-text-secondary);cursor:pointer;line-height:1.45}
.lw-form-check input{flex-shrink:0;margin-top:.15rem;accent-color:var(--lw-accent)}
.lw-inline-link{color:var(--lw-accent);font-weight:600;text-decoration:none}
.lw-inline-link:hover{text-decoration:underline;color:var(--lw-accent-hover)}
.lw-form-actions{margin-top:.5rem}
.lw-form-actions .lw-btn-primary,.lw-form-actions .lw-btn-secondary{width:100%;justify-content:center;padding:.6875rem 1.25rem;font-size:.875rem;border-radius:.75rem}
@media(min-width:480px){.lw-form-actions--row{flex-direction:row;flex-wrap:wrap}.lw-form-actions--row .lw-btn-primary,.lw-form-actions--row .lw-btn-secondary{width:auto}}
.lw-form-file{font-size:.8125rem}
.lw-form-callout{border-radius:.75rem;border:1px solid var(--lw-border-accent);background:var(--lw-bg-accent-muted);padding:.75rem 1rem;font-size:.8125rem;color:var(--lw-text-secondary);line-height:1.5;margin:0 0 .75rem}
.lw-form-callout--warn{border-color:#fde68a;background:var(--lw-stat-warn);color:#92400e}
.lw-form-callout--hidden{display:none}
.lw-form-callout-title{margin:0 0 .5rem;font-weight:600;color:var(--lw-text-strong)}
.lw-form-callout-list{margin:0;padding-left:1.25rem;list-style-type:decimal}
.lw-form-callout-list li+li{margin-top:.25rem}
.lw-empty-state,.lw-panel-empty,.lw-admin-empty{padding:2.5rem 1.5rem;text-align:center;background:var(--lw-bg-card);border:1px dashed var(--lw-border);border-radius:.75rem}
.lw-empty-state-title,.lw-panel-empty-title,.lw-admin-empty-title{margin:0;font-size:.9375rem;font-weight:600;color:var(--lw-text-secondary)}
.lw-empty-state-desc,.lw-panel-empty-desc,.lw-admin-empty-desc{margin:.5rem 0 0;font-size:.8125rem;color:var(--lw-text-muted);max-width:24rem;margin-left:auto;margin-right:auto}
.lw-table-wrap{width:100%;max-width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;min-width:0}
.lw-form--labeled .lw-form-field{margin-bottom:.875rem}
.lw-form--labeled .lw-form-field:last-child{margin-bottom:0}
.lw-form-grid--labeled{display:grid;gap:.875rem 0;grid-template-columns:1fr}
.lw-form-grid--labeled>.lw-form-field{margin-bottom:0}
.lw-form-grid--labeled>.lw-form-field--span2{display:flex;flex-direction:column;gap:.35rem}
.lw-form--labeled .lw-form-field--span2{display:flex;flex-direction:column;gap:.35rem}
@media(min-width:640px){
.lw-form-grid--labeled>.lw-form-field:not(.lw-form-field--span2){display:grid;grid-template-columns:var(--lw-form-label-col) minmax(0,1fr);gap:.35rem 1rem;align-items:start}
.lw-form-grid--labeled>.lw-form-field:not(.lw-form-field--span2)>.lw-form-label{margin-bottom:0;padding-top:.5rem;line-height:1.4;max-width:var(--lw-form-label-col);overflow-wrap:break-word}
.lw-form-grid--labeled>.lw-form-field:not(.lw-form-field--span2)>.lw-form-hint,
.lw-form-grid--labeled>.lw-form-field:not(.lw-form-field--span2)>.lw-form-error{grid-column:2}
.lw-form--labeled .lw-form-field:not(.lw-form-field--span2){display:grid;grid-template-columns:var(--lw-form-label-col) minmax(0,1fr);gap:.35rem 1rem;align-items:start;margin-bottom:.875rem}
.lw-form--labeled .lw-form-field:not(.lw-form-field--span2)>.lw-form-label{margin-bottom:0;padding-top:.5rem;line-height:1.4;max-width:var(--lw-form-label-col);overflow-wrap:break-word}
.lw-form--labeled .lw-form-field:not(.lw-form-field--span2)>.lw-form-hint,
.lw-form--labeled .lw-form-field:not(.lw-form-field--span2)>.lw-form-error{grid-column:2}
.lw-form--labeled .lw-household-recap-fields{grid-template-columns:repeat(2,minmax(0,1fr));column-gap:1.25rem;row-gap:1rem}
.lw-form--labeled .lw-household-recap-fields>.lw-form-field:not(.lw-form-field--span2){display:flex;flex-direction:column;gap:.25rem;align-items:stretch;margin-bottom:0}
.lw-form--labeled .lw-household-recap-fields>.lw-form-field:not(.lw-form-field--span2)>.lw-form-label{padding-top:0;max-width:none}
.lw-form--labeled .lw-form-field--check-row{grid-column:1/-1;display:flex;flex-direction:column;gap:.35rem;margin-bottom:0}
.lw-form--labeled .lw-form-field--check-row .lw-form-check{max-width:100%}
.lw-form--labeled .lw-form-field--check-row input[disabled]{cursor:default;opacity:1}
}

/* Hub & form login (/akses-pengurus) */
.lw-auth-hub{margin-bottom:1rem}
.lw-auth-hub-head{margin-bottom:1.5rem;text-align:center;align-items:center}
.lw-auth-hub-head--compact{text-align:left;margin-bottom:1rem;align-items:flex-start}
.lw-auth-hub-lead{margin:0;max-width:32rem;font-size:.875rem;color:var(--lw-text-muted);line-height:1.55}
.lw-auth-hub-head--compact .lw-auth-hub-lead{margin-left:0;margin-right:0}
.lw-auth-portal-grid{display:grid;gap:1rem}
@media(min-width:640px){.lw-auth-portal-grid{grid-template-columns:repeat(2,1fr);gap:1.125rem}}
@media(min-width:1024px){.lw-auth-portal-grid{grid-template-columns:repeat(3,1fr)}}
.lw-auth-portal-card{display:flex;flex-direction:column;align-items:center;text-align:center;text-decoration:none;color:inherit;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:1rem;padding:1.5rem 1.25rem;box-shadow:var(--lw-shadow-sm);transition:transform .18s ease,box-shadow .18s,border-color .18s;-webkit-tap-highlight-color:transparent;height:100%}
.lw-auth-portal-card:hover{border-color:#6ee7b7;box-shadow:0 12px 32px rgba(6,78,59,.14);transform:translateY(-2px)}
.lw-auth-portal-icon{display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;border-radius:9999px;margin-bottom:.875rem}
.lw-auth-portal-icon--rt{background:#d1fae5;color:var(--lw-accent-text)}
.lw-auth-portal-icon--kel{background:#dbeafe;color:#1d4ed8}
.lw-auth-portal-icon--admin{background:var(--lw-bg-subtle);color:var(--lw-text-body)}
.lw-auth-portal-name{margin:0;font-size:1rem;font-weight:700;color:var(--lw-text)}
.lw-auth-portal-desc{margin:.5rem 0 0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.5;flex-grow:1}
.lw-auth-portal-arrow{margin-top:.875rem;font-size:.75rem;font-weight:700;color:var(--lw-accent)}
.lw-auth-login{max-width:28rem;margin:0 auto}
.lw-auth-back{display:inline-block;margin-bottom:1rem;font-size:.8125rem;font-weight:600;color:var(--lw-accent);text-decoration:none}
.lw-auth-back:hover{text-decoration:underline}
.lw-auth-form-card{border-radius:1rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);padding:1.5rem;box-shadow:0 8px 24px rgba(15,23,42,.06)}
.lw-auth-page{margin-left:auto;margin-right:auto}
.lw-auth-card{padding:1.5rem 1.25rem;border-radius:1rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);box-shadow:var(--lw-shadow-sm)}
@media(min-width:640px){.lw-auth-card{padding:1.75rem 1.5rem}}
.lw-auth-header{text-align:center;margin-bottom:0}
.lw-auth-label{display:block;text-align:center;font-size:.875rem;font-weight:600;color:var(--lw-text-secondary);margin-bottom:.35rem}
.lw-auth-foot{margin-top:1.25rem;text-align:center;font-size:.875rem;color:var(--lw-text-muted);line-height:1.5}
.lw-auth-roles{margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--lw-border-soft);font-size:.8125rem;color:var(--lw-text-muted);line-height:1.5}
.lw-auth-roles-title{margin:0 0 .5rem;font-weight:600;color:var(--lw-text-secondary);font-size:.8125rem}
.lw-auth-roles-list{margin:0;padding:0 0 0 1.125rem;list-style:disc}
.lw-auth-roles-list li+li{margin-top:.35rem}
.lw-auth-roles-list strong{color:var(--lw-text-body);font-weight:600}

/* Login pengurus dua kolom (/akses-pengurus) */
.lw-auth-split.lw-auth-page{max-width:min(64rem,100%);width:100%;margin-inline:auto}
.lw-auth-split{width:100%;border-radius:var(--lw-radius-surface);background:linear-gradient(135deg,#f0fdfa 0%,#ecfeff 48%,#ccfbf1 100%);border:1px solid var(--lw-border-soft);padding:clamp(1rem,2.5vw,1.5rem);box-sizing:border-box}
.lw-auth-split__grid{display:grid;gap:clamp(1rem,2.5vw,1.5rem);width:100%;margin-inline:auto;align-items:start}
@media(min-width:768px){.lw-auth-split__grid{grid-template-columns:minmax(0,1fr) minmax(0,1.15fr);gap:1.5rem}}
.lw-auth-split__illust{display:flex;min-width:0}
.lw-auth-split__illust-inner{display:flex;flex-direction:column;gap:1rem;width:100%;padding:clamp(1rem,2vw,1.25rem);border-radius:1.25rem;background:rgba(255,255,255,.55);border:1px solid rgba(153,246,228,.6);box-shadow:var(--lw-shadow-sm)}
@media(min-width:768px){.lw-auth-split__illust-inner{flex-direction:row;align-items:center;gap:1.25rem}}
.lw-auth-split__illust-media{flex-shrink:0;min-width:0}
@media(min-width:768px){.lw-auth-split__illust-media{flex:0 0 42%;max-width:42%}}
.lw-auth-split__svg{display:block;width:100%;max-width:100%;margin-inline:auto;height:auto;max-height:10rem}
@media(min-width:768px){.lw-auth-split__svg{max-height:none;margin:0}}
.lw-auth-split__illust-body{flex:1;min-width:0;text-align:left}
.lw-auth-split__illust-copy{text-align:left}
.lw-auth-split__illust-title{margin:0;font-size:clamp(1.0625rem,2.5vw,1.25rem);font-weight:800;color:var(--lw-accent-dark);line-height:1.3;letter-spacing:-.02em}
.lw-auth-split__illust-list{margin:.75rem 0 0;padding:0 0 0 1.125rem;font-size:.8125rem;color:var(--lw-text-secondary);line-height:1.55}
.lw-auth-split__illust-list li+li{margin-top:.4rem}
.lw-auth-split__card{display:flex;flex-direction:column;background:#fff;border-radius:1.375rem;border:1px solid var(--lw-border);box-shadow:var(--lw-shadow-md);padding:clamp(1.5rem,4vw,2.25rem);min-width:0}
.lw-auth-split__head{margin-bottom:0}
.lw-auth-split__title{margin:0;font-size:clamp(1.25rem,3vw,1.5rem);font-weight:800;color:var(--lw-accent-dark);line-height:1.25;letter-spacing:-.02em}
.lw-auth-split__lead{margin:.625rem 0 0;font-size:.875rem;color:var(--lw-text-muted);line-height:1.55}
.lw-auth-split__alert{margin-top:1rem}
.lw-auth-split__form{display:flex;flex-direction:column;gap:1rem;margin-top:1.25rem}
.lw-auth-split__form .lw-form-field{margin:0}
.lw-auth-split__form .lw-form-input{min-height:2.75rem;padding:.625rem .875rem;border-radius:.625rem;font-size:.9375rem}
.lw-auth-split__password-wrap{position:relative}
.lw-auth-split__password-input{padding-right:2.75rem}
.lw-auth-split__password-toggle{position:absolute;right:.5rem;top:50%;transform:translateY(-50%);display:inline-flex;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;padding:0;border:none;border-radius:.5rem;background:transparent;color:var(--lw-text-muted);cursor:pointer;transition:color .15s,background .15s}
.lw-auth-split__password-toggle:hover{color:var(--lw-accent);background:var(--lw-bg-accent-soft)}
.lw-auth-split__password-toggle:focus-visible{outline:2px solid var(--lw-accent);outline-offset:2px}
.lw-auth-split__eye{display:block}
.lw-auth-split__eye[hidden]{display:none}
.lw-auth-split__remember{margin:0}
.lw-auth-split__submit{display:flex;align-items:center;justify-content:center;width:100%;min-height:2.75rem;margin-top:.25rem;padding:.75rem 1.25rem;font-size:.9375rem;font-weight:700;color:#fff;background:var(--lw-green-dark);border:none;border-radius:.75rem;cursor:pointer;transition:background .15s,box-shadow .15s;box-shadow:0 4px 14px rgba(6,78,59,.22)}
.lw-auth-split__submit:hover{background:#047857;box-shadow:0 6px 18px rgba(6,78,59,.28)}
.lw-auth-split__submit:focus-visible{outline:2px solid var(--lw-accent-bright);outline-offset:2px}
.lw-auth-split__note{margin:1.25rem 0 0;font-size:.75rem;color:var(--lw-text-faint);line-height:1.5;text-align:center}
@media(min-width:768px){.lw-auth-split__note{text-align:left}}

/* Lacak permohonan (/lacak) — layout modern */
.lw-track-page{background:linear-gradient(160deg,#f0fdfa 0%,#ecfeff 42%,#e6fffa 100%)}
.lw-contact-page.lw-contact-split{background:linear-gradient(160deg,#f0fdfa 0%,#ecfeff 42%,#e6fffa 100%)}
.lw-track-page.lw-track-split{max-width:none;width:100%;margin-inline:0}
.lw-contact-page.lw-contact-split{max-width:none;width:100%;margin-inline:0}
.lw-track-page.lw-track-split .lw-track-form-card{max-width:none;margin-inline:0}
.lw-contact-page.lw-contact-split .lw-contact-form-card{max-width:none;margin-inline:0}
.lw-page-inner .lw-track-page.lw-track-split{margin-inline:calc(-1 * var(--lw-content-gutter));width:calc(100% + 2 * var(--lw-content-gutter));padding-inline:var(--lw-content-gutter);box-sizing:border-box}
.lw-page-inner .lw-contact-page.lw-contact-split{margin-inline:calc(-1 * var(--lw-content-gutter));width:calc(100% + 2 * var(--lw-content-gutter));padding-inline:var(--lw-content-gutter);box-sizing:border-box}
.lw-track-page .lw-track-board{gap:clamp(1.5rem,3vw,2.5rem);padding-bottom:clamp(1.5rem,3vw,2.5rem)}
.lw-track-board--centered{display:flex;flex-direction:column;align-items:center}
.lw-track-hero-grid{display:grid;gap:clamp(1.5rem,3vw,2.5rem);width:100%;align-items:start}
@media(max-width:1023px){.lw-track-hero-grid{display:flex;flex-direction:column;align-items:stretch;gap:1.5rem}.lw-track-intro,.lw-track-forms,.lw-contact-forms,.lw-track-page .lw-track-form-card,.lw-contact-page .lw-contact-form-card,.lw-auth-forms,.lw-auth-split .lw-auth-form-card{width:100%;max-width:none}.lw-track-forms,.lw-contact-forms,.lw-auth-forms{order:-1}}
@media(min-width:1024px){.lw-track-hero-grid{grid-template-columns:minmax(0,1fr) minmax(0,1.05fr);gap:clamp(1.75rem,3vw,2.5rem)}}
.lw-track-hero-grid.lw-track-hero-grid--solo{max-width:36rem;margin-inline:auto;width:100%}
@media(min-width:1024px){.lw-track-hero-grid.lw-track-hero-grid--solo{grid-template-columns:1fr}}
.lw-track-intro{display:flex;flex-direction:column;gap:1.25rem;padding:.5rem 0;min-width:0}
@media(min-width:768px){.lw-track-intro{padding-top:1rem;padding-right:1rem}}
.lw-track-intro__title{margin:0;font-size:clamp(1.5rem,4vw,2rem);font-weight:800;color:var(--lw-accent-dark);line-height:1.2;letter-spacing:-.03em}
.lw-track-intro__lead{margin:0;font-size:clamp(1rem,2vw,1.125rem);color:var(--lw-text-secondary);line-height:1.6}
.lw-track-benefits{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:1rem}
.lw-track-benefit{display:flex;align-items:flex-start;gap:1rem}
.lw-track-benefit__icon{display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;width:2.75rem;height:2.75rem;border-radius:9999px;background:#ecfdf5;color:var(--lw-accent);border:1px solid rgba(94,234,212,.5)}
.lw-track-benefit__text{display:flex;flex-direction:column;gap:.2rem;min-width:0}
.lw-track-benefit__text strong{font-size:clamp(.9375rem,2vw,1.0625rem);font-weight:700;color:var(--lw-accent-dark);line-height:1.35}
.lw-track-benefit__text span{font-size:clamp(.875rem,1.8vw,.9375rem);color:var(--lw-text-muted);line-height:1.5}
.lw-track-forms{display:flex;flex-direction:column;min-width:0}
.lw-contact-forms{display:flex;flex-direction:column;min-width:0}
.lw-auth-forms{display:flex;flex-direction:column;min-width:0}
.lw-auth-split .lw-auth-form-card{margin:0;max-width:100%;padding:clamp(1.5rem,3vw,2rem);background:#fff;border:1px solid var(--lw-border);border-radius:1.5rem;box-shadow:var(--lw-shadow-md)}
.lw-auth-split .lw-track-split__head{margin-bottom:1.25rem;padding-bottom:1rem}
.lw-auth-page-wrapper .lw-auth-board .lw-auth-back{margin-bottom:0}
.lw-track-bottom-grid{display:grid;gap:clamp(1.25rem,2.5vw,1.75rem);width:100%}
@media(min-width:1024px){.lw-track-bottom-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
.lw-track-bottom-grid.lw-track-bottom-grid--solo{max-width:36rem;margin-inline:auto;width:100%;grid-template-columns:1fr}
@media(min-width:1024px){.lw-track-bottom-grid.lw-track-bottom-grid--solo{grid-template-columns:1fr}}
.lw-track-info-card{display:flex;flex-direction:column;gap:1rem;padding:clamp(1.5rem,3vw,2rem);background:#fff;border:1px solid var(--lw-border);border-radius:1.375rem;box-shadow:var(--lw-shadow-md);min-width:0;overflow-wrap:break-word}
.lw-track-info-card__title{margin:0;font-size:clamp(1.125rem,2.5vw,1.375rem);font-weight:800;color:var(--lw-accent-dark);line-height:1.3;letter-spacing:-.02em}
.lw-track-info-card--faq{gap:1rem;padding:clamp(1.25rem,2.5vw,1.75rem)}
.lw-track-info-card--faq .lw-faq-section--track{margin:0}
.lw-track-info-card--faq .lw-profile-section-head{margin-bottom:.75rem}
.lw-track-info-card--faq .lw-section-title{font-size:clamp(1.125rem,2.5vw,1.375rem)}
.lw-track-page .lw-track-form-card{margin:0;max-width:100%;padding:clamp(1.5rem,3vw,2rem);background:#fff;border:1px solid var(--lw-border);border-radius:1.5rem;box-shadow:var(--lw-shadow-md)}
.lw-contact-page .lw-contact-form-card{margin:0;max-width:100%;padding:clamp(1.5rem,3vw,2rem);background:#fff;border:1px solid var(--lw-border);border-radius:1.5rem;box-shadow:var(--lw-shadow-md)}
.lw-contact-page .lw-track-split__form .lw-form-label{font-size:clamp(.9375rem,2vw,1.0625rem);font-weight:600;color:var(--lw-text-strong)}
.lw-contact-page .lw-track-split__form .lw-form-input,.lw-contact-page .lw-track-split__form .lw-form-select,.lw-contact-page .lw-track-split__form .lw-form-textarea{min-height:3.25rem;padding:.875rem 1.125rem;border-radius:.875rem;font-size:clamp(1rem,2vw,1.0625rem);border:1px solid var(--lw-border);background:#fff;transition:border-color .15s,box-shadow .15s}
.lw-contact-page .lw-track-split__form .lw-form-textarea{min-height:8rem}
.lw-contact-page .lw-track-split__form .lw-form-input:focus,.lw-contact-page .lw-track-split__form .lw-form-select:focus,.lw-contact-page .lw-track-split__form .lw-form-textarea:focus{border-color:var(--lw-accent);box-shadow:0 0 0 3px rgba(15,118,110,.15);outline:none}
.lw-track-split__badge{display:inline-block;width:fit-content;margin:0 0 1rem;padding:.3rem .75rem;font-size:.6875rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--lw-accent-dark);background:#ecfdf5;border:1px solid rgba(94,234,212,.6);border-radius:9999px}
.lw-track-split__head{margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--lw-border-soft)}
.lw-track-split__title{margin:0;font-size:clamp(1.375rem,3.5vw,1.75rem);font-weight:800;color:var(--lw-accent-dark);line-height:1.25;letter-spacing:-.02em}
.lw-track-split__lead{margin:.75rem 0 0;font-size:clamp(.9375rem,2vw,1.0625rem);color:var(--lw-text-muted);line-height:1.6}
.lw-track-split__alert{margin:0 0 1.25rem}
.lw-track-split__form{display:flex;flex-direction:column;gap:1.5rem;margin-top:0}
.lw-track-split__form .lw-form-field{margin:0}
.lw-track-page .lw-track-split__form .lw-form-label,.lw-track-page .lw-track-split__alt-form .lw-form-label{font-size:clamp(.9375rem,2vw,1.0625rem);font-weight:600;color:var(--lw-text-strong)}
.lw-track-page .lw-track-split__form .lw-form-input,.lw-track-page .lw-track-split__alt-form .lw-form-input{min-height:3.25rem;padding:.875rem 1.125rem;border-radius:.875rem;font-size:clamp(1rem,2vw,1.0625rem);border:1px solid var(--lw-border);background:#fff;transition:border-color .15s,box-shadow .15s}
.lw-track-page .lw-track-split__form .lw-form-input:focus,.lw-track-page .lw-track-split__alt-form .lw-form-input:focus{border-color:var(--lw-accent);box-shadow:0 0 0 3px rgba(15,118,110,.15);outline:none}
.lw-track-split__mono{text-align:left;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;letter-spacing:.02em}
.lw-track-split__submit{display:flex;align-items:center;justify-content:center;width:100%;min-height:3.25rem;margin-top:.25rem;padding:1rem 1.5rem;font-size:clamp(1rem,2vw,1.0625rem);font-weight:700;color:#fff;background:var(--lw-green-dark);border:none;border-radius:.875rem;cursor:pointer;transition:background .15s,box-shadow .15s;box-shadow:0 4px 18px rgba(6,78,59,.22)}
.lw-track-split__submit:hover{background:#047857;box-shadow:0 6px 20px rgba(6,78,59,.28)}
.lw-track-split__submit:focus-visible{outline:2px solid var(--lw-accent-bright);outline-offset:2px}
.lw-track-split__submit--sm{margin-top:.875rem;min-height:3rem;font-size:clamp(.9375rem,2vw,1rem)}
.lw-track-divider{margin:1.5rem 0 0;padding-top:1.5rem;border-top:1px solid var(--lw-border-soft);font-size:.875rem;font-weight:600;text-align:center;color:var(--lw-text-faint);letter-spacing:.02em}
.lw-track-form-card .lw-track-alt{margin-top:1rem;border-top:none;padding-top:0}
.lw-track-form-card .lw-track-alt+.lw-track-alt{margin-top:.75rem;padding-top:0;border-top:none}
.lw-track-form-card .lw-track-alt summary{font-size:clamp(.9375rem,2vw,1.0625rem);font-weight:600;color:var(--lw-accent);cursor:pointer;list-style:none;padding:.75rem 1rem;border-radius:.875rem;background:var(--lw-bg-accent-soft);border:1px solid var(--lw-border-soft);transition:background .15s,border-color .15s}
.lw-track-form-card .lw-track-alt summary:hover{background:#ecfdf5;border-color:var(--lw-border-accent)}
.lw-track-form-card .lw-track-alt summary::-webkit-details-marker{display:none}
.lw-track-form-card .lw-track-alt[open] summary{margin-bottom:1rem;background:#ecfdf5;border-color:var(--lw-border-accent)}
.lw-track-steps-note{margin:0;font-size:.875rem;color:var(--lw-text-muted);line-height:1.6}
.lw-track-steps{display:flex;flex-direction:column;gap:0;margin:0;padding:0;list-style:none}
.lw-track-step{display:flex;align-items:flex-start;gap:.75rem;min-width:0;padding:.75rem 0;border-bottom:1px solid var(--lw-border-soft)}
.lw-track-step:first-child{padding-top:0}
.lw-track-step:last-child{padding-bottom:0;border-bottom:none}
.lw-track-step__num{display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;width:1.5rem;height:1.5rem;border-radius:9999px;background:var(--lw-bg-accent-soft);color:var(--lw-accent);font-size:.8125rem;font-weight:700;line-height:1}
.lw-track-step__text{flex:1;min-width:0;font-size:.9375rem;font-weight:600;color:var(--lw-text-strong);line-height:1.5}
.lw-track-split__alt-form{display:flex;flex-direction:column;gap:1rem;margin-top:0}
.lw-track-split__foot{margin:0;font-size:.875rem;color:var(--lw-text-muted);line-height:1.6}
.lw-faq-section--track .lw-home-faq-list{max-width:none;gap:.625rem}
.lw-faq-section--track .lw-home-faq-item{border-radius:.875rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);overflow:hidden;transition:border-color .2s}
.lw-faq-section--track .lw-home-faq-item[open]{border-color:var(--lw-border-accent-strong);box-shadow:var(--lw-shadow-sm)}
.lw-faq-section--track .lw-home-faq-question{cursor:pointer;padding:1rem 1.125rem;font-size:.9375rem;font-weight:600;color:var(--lw-text-strong);list-style:none;display:flex;align-items:flex-start;gap:.75rem;line-height:1.45}
.lw-faq-section--track .lw-home-faq-answer{padding:0 1.125rem 1rem 1.125rem}
.lw-faq-section--track .lw-home-faq-answer p{margin:0;font-size:.875rem;line-height:1.6;color:var(--lw-text-secondary)}
@media(max-width:480px){
.lw-faq-section--track .lw-home-faq-question{padding:.875rem 1rem;font-size:.875rem}
.lw-faq-section--track .lw-home-faq-answer{padding:0 1rem .875rem}
}

/* Form pendataan publik */
.lw-pendataan-form fieldset{border:none;padding:0;margin:0}
.lw-pendataan-section{padding-top:.25rem;border-top:1px solid var(--lw-border-soft)}
.lw-pendataan-member{border:1px solid var(--lw-border);border-radius:.75rem;padding:1rem}
.lw-pendataan-member-title{margin:0;font-size:.875rem;font-weight:600;color:var(--lw-accent-text)}
.lw-pendataan-member-grid{margin-top:.75rem}
.lw-pendataan-member-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem}
.lw-pendataan-member input:disabled{background:var(--lw-bg-muted);color:var(--lw-text-faint)}
.lw-form-hint--accent{color:var(--lw-accent)}
.lw-form-hint--narrow{margin-top:1rem;max-width:42rem}
.lw-success-actions{justify-content:center}
.lw-success-actions--center{margin-top:1.5rem}
.lw-success-label{text-transform:uppercase;letter-spacing:.04em}
.lw-home-faq-question{min-height:2.75rem}
.lw-pendataan-member-count{font-size:.8125rem;font-weight:600;color:var(--lw-accent)}
.lw-pendataan-add-btn{display:inline-flex;align-items:center;justify-content:center;padding:.5rem 1.125rem;font-size:.875rem;font-weight:600;color:#fff;background:var(--lw-accent);border:none;border-radius:.5rem;cursor:pointer;transition:background .2s}
.lw-pendataan-add-btn:hover:not(:disabled){background:var(--lw-accent-hover)}
.lw-pendataan-add-btn:disabled{opacity:.5;cursor:not-allowed}
.lw-pendataan-remove-btn{flex-shrink:0;padding:.25rem .625rem;font-size:.75rem;font-weight:600;color:#b45309;background:var(--lw-bg-card);border:1px solid #fcd34d;border-radius:.375rem;cursor:pointer}
.lw-pendataan-remove-btn:hover{background:#fef3c7}
.lw-pendataan-success{padding:2rem 1rem}
.lw-mb-3{margin-bottom:.75rem}
.lw-mx-auto{margin-left:auto;margin-right:auto}
.lw-mt-5{margin-top:1.25rem}
.lw-pendataan-success-icon{width:3.5rem;height:3.5rem;border-radius:9999px;background:#d1fae5;color:var(--lw-accent);font-size:1.5rem;font-weight:800;display:flex;align-items:center;justify-content:center}

/* ——— Panel pengurus (RT / Monitoring / Admin) ——— */
.lw-panel-body{margin:0;background:var(--lw-bg-panel);color:var(--lw-text);-webkit-font-smoothing:antialiased}
.lw-panel-menu-toggle{position:absolute;opacity:0;width:0;height:0;pointer-events:none}
.lw-panel-layout{display:flex;min-height:100vh;position:relative}
.lw-panel-backdrop{display:none;position:fixed;inset:0;z-index:40;background:var(--lw-overlay);cursor:pointer}
.lw-panel-menu-toggle:checked~.lw-panel-backdrop{display:block}
.lw-panel-sidebar{position:fixed;inset:0 auto 0 0;z-index:50;width:17rem;max-width:88vw;background:linear-gradient(180deg,var(--lw-accent-dark),var(--lw-accent));color:#fff;transform:translateX(-100%);transition:transform .22s ease;box-shadow:4px 0 24px rgba(15,118,110,.2);flex-shrink:0;overflow:hidden}
.lw-panel-menu-toggle:checked~.lw-panel-sidebar{transform:translateX(0)}
.lw-panel-sidebar-inner{display:flex;flex-direction:column;height:100vh;max-height:100vh;overflow:hidden}
.lw-panel-brand{padding:1rem;border-bottom:1px solid rgba(255,255,255,.12);flex-shrink:0}
.lw-panel-brand-eyebrow{margin:0;font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:rgba(236,253,245,.75)}
.lw-panel-brand-title{margin:.35rem 0 0;font-size:1rem;font-weight:700;line-height:1.35;color:#fff}
.lw-panel-brand-sub{margin:.25rem 0 0;font-size:.75rem;color:rgba(236,253,245,.8)}
.lw-panel-date{margin:.5rem 0 0;padding:.375rem .5rem;border-radius:.5rem;background:rgba(0,0,0,.15);font-size:.8125rem;line-height:1.35;color:#ecfdf5}
.lw-panel-date-label{display:block;font-size:.625rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:rgba(236,253,245,.7);margin-bottom:.15rem}
.lw-panel-nav{flex:1;min-height:0;overflow:hidden;padding:.5rem;display:flex;flex-direction:column;gap:.0625rem}
.lw-panel-nav-link{display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.45rem .625rem;border-radius:.5rem;font-size:.875rem;font-weight:500;color:rgba(236,253,245,.95);text-decoration:none;transition:background .15s,color .15s}
.lw-panel-nav-link:hover{background:rgba(255,255,255,.12);color:#fff}
.lw-panel-nav-link--active{background:rgba(255,255,255,.2);color:#fff;font-weight:600;box-shadow:inset 3px 0 0 #a7f3d0}
.lw-panel-badge{flex-shrink:0;min-width:1.35rem;padding:.1rem .45rem;border-radius:9999px;background:#fbbf24;color:#78350f;font-size:.6875rem;font-weight:700;text-align:center}
.lw-panel-badge--muted{background:rgba(255,255,255,.25);color:#fff}
.lw-panel-user{margin-top:auto;padding:.75rem;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0}
.lw-panel-user-name{margin:0;font-size:.9375rem;font-weight:600;color:#fff;line-height:1.3}
.lw-panel-user-role{margin:.2rem 0 0;font-size:.75rem;color:rgba(236,253,245,.85)}
.lw-panel-user-email{margin:.35rem 0 0;font-size:.6875rem;color:rgba(236,253,245,.65);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lw-panel-logout-btn{display:block;width:100%;margin-top:.5rem;padding:.5rem 1rem;font-size:.875rem;font-weight:600;text-align:center;color:#fff;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.28);border-radius:.5rem;cursor:pointer;transition:background .2s,border-color .2s}
.lw-panel-logout-btn:hover{background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.45)}
.lw-panel-logout-btn--compact{width:auto;margin-top:0;padding:.4rem .75rem;font-size:.75rem;white-space:nowrap}
.lw-panel-main{flex:1;min-width:0;display:flex;flex-direction:column;min-height:100vh}
.lw-panel-topbar{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;background:var(--lw-bg-card);border-bottom:1px solid var(--lw-border);position:sticky;top:0;z-index:30}
.lw-panel-menu-btn{display:flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.5rem;background:var(--lw-bg-accent-soft);border:1px solid var(--lw-border-accent-strong);cursor:pointer;flex-shrink:0}
.lw-panel-menu-icon{display:block;width:1.125rem;height:2px;background:var(--lw-accent);border-radius:1px;box-shadow:0 -5px 0 var(--lw-accent),0 5px 0 var(--lw-accent)}
.lw-panel-topbar-center{flex:1;min-width:0}
.lw-panel-topbar-title{margin:0;font-size:1rem;font-weight:700;color:var(--lw-text-strong);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lw-panel-topbar-logout{flex-shrink:0}
.lw-panel-content{flex:1;padding:1rem 1rem 2rem;width:100%;max-width:72rem;margin:0 auto}
.lw-panel-alert{margin-bottom:1rem;padding:.75rem 1rem;border-radius:.5rem;font-size:.875rem}
.lw-panel-alert--success{background:var(--lw-bg-accent-soft);border:1px solid var(--lw-border-accent-strong);color:var(--lw-accent-text)}
.lw-panel-alert--info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}
.lw-panel-alert--warn{background:var(--lw-stat-warn);border:1px solid #fde68a;color:#92400e}
.lw-panel-alert--error{background:var(--lw-alert-error-bg);border:1px solid var(--lw-alert-error-border);color:var(--lw-alert-error-text)}
.lw-panel-page-head{margin-bottom:1.5rem}
.lw-panel-page-eyebrow{margin:0;font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--lw-accent)}
.lw-panel-page-title{margin:.35rem 0 0;font-size:1.5rem;font-weight:800;color:var(--lw-accent-dark);line-height:1.2}
.lw-panel-page-lead{margin:.5rem 0 0;font-size:.875rem;color:var(--lw-text-muted);max-width:40rem;line-height:1.5}
.lw-panel-stats{display:grid;grid-template-columns:repeat(2,1fr);gap:.75rem;margin-bottom:1.5rem}
@media(min-width:640px){.lw-panel-stats{grid-template-columns:repeat(3,1fr)}}
@media(min-width:1024px){.lw-panel-stats{grid-template-columns:repeat(5,1fr)}}
.lw-panel-stat{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;padding:1rem;box-shadow:0 1px 3px rgba(15,23,42,.04)}
.lw-panel-stat--highlight{border-color:#a7f3d0;background:var(--lw-stat-highlight)}
.lw-panel-stat--warn{border-color:#fcd34d;background:var(--lw-stat-warn)}
.lw-panel-stat-label{margin:0;font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--lw-text-muted)}
.lw-panel-stat-value{margin:.35rem 0 0;font-size:1.5rem;font-weight:800;color:var(--lw-accent-dark);line-height:1.1}
.lw-panel-section-title{margin:0 0 .75rem;font-size:1rem;font-weight:700;color:var(--lw-text)}
.lw-panel-section-title--flush{margin:0}
.lw-panel-section-title--danger{color:#991b1b}
.lw-panel-section{margin-bottom:1.75rem}
.lw-panel-chart-section{margin-bottom:1.75rem}
.lw-panel-chart-lead{margin:-.25rem 0 1rem;font-size:.8125rem;line-height:1.5;color:var(--lw-text-muted)}
.lw-panel-chart-card{padding:1.25rem 1.375rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;box-shadow:var(--lw-shadow-sm)}
.lw-panel-chart-layout{display:flex;flex-wrap:wrap;align-items:center;gap:1.5rem 2rem}
.lw-panel-pie{flex-shrink:0;width:9rem;height:9rem;border-radius:9999px;border:3px solid var(--lw-border-accent);box-shadow:0 4px 16px rgba(6,78,59,.12);background:var(--lw-bg-muted)}
.lw-panel-pie--gender{background:conic-gradient(var(--lw-accent-dark) 0 calc(var(--pie-male-end,0) * 1%),var(--lw-accent-bright) calc(var(--pie-male-end,0) * 1%) 100%)}
.lw-panel-pie-legend{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:.875rem;min-width:12rem}
.lw-panel-pie-legend-item{display:flex;align-items:flex-start;gap:.625rem}
.lw-panel-pie-swatch{flex-shrink:0;width:.875rem;height:.875rem;border-radius:.25rem;margin-top:.2rem}
.lw-panel-pie-swatch--male{background:var(--lw-accent-dark)}
.lw-panel-pie-swatch--female{background:var(--lw-accent-bright);border:1px solid var(--lw-border-accent-strong)}
.lw-panel-pie-legend-body{display:flex;flex-direction:column;gap:.15rem}
.lw-panel-pie-legend-label{font-size:.875rem;font-weight:700;color:var(--lw-text-strong)}
.lw-panel-pie-legend-value{font-size:.8125rem;color:var(--lw-text-muted)}
.lw-panel-chart-note{font-size:.75rem;line-height:1.45;color:var(--lw-text-muted)}
.lw-panel-quick{margin-bottom:1.75rem}
.lw-panel-quick-grid{display:grid;gap:.75rem}
@media(min-width:640px){.lw-panel-quick-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:1024px){.lw-panel-quick-grid{grid-template-columns:repeat(3,1fr)}}
.lw-panel-quick-card{display:flex;flex-direction:column;gap:.25rem;padding:1rem 1.125rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;text-decoration:none;color:inherit;transition:border-color .15s,box-shadow .15s}
.lw-panel-quick-card:hover{border-color:#6ee7b7;box-shadow:0 4px 14px rgba(6,78,59,.08)}
.lw-panel-quick-name{font-size:.9375rem;font-weight:600;color:var(--lw-accent-dark)}
.lw-panel-quick-desc{font-size:.75rem;color:var(--lw-text-muted);line-height:1.45}
.lw-panel-quick-badge{align-self:flex-start;margin-top:.25rem;font-size:.625rem;font-weight:700;color:#b45309;background:#fef3c7;padding:.15rem .5rem;border-radius:9999px}
.lw-panel-table-wrap{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;overflow-x:auto;-webkit-overflow-scrolling:touch}
.lw-panel-table{width:100%;min-width:36rem;font-size:.875rem;border-collapse:collapse}
@media(max-width:639px){.lw-panel-table{font-size:.8125rem}.lw-panel-table th,.lw-panel-table td{padding:.5rem .625rem}}
.lw-panel-table thead{background:var(--lw-bg-muted)}
.lw-panel-table th{padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--lw-text-muted);border-bottom:1px solid var(--lw-border)}
.lw-panel-table td{padding:.75rem 1rem;border-bottom:1px solid var(--lw-border-soft);color:var(--lw-text-body)}
.lw-panel-table tbody tr:last-child td{border-bottom:none}
.lw-panel-table-link{font-family:ui-monospace,monospace;font-size:.8125rem;font-weight:600;color:var(--lw-accent);text-decoration:none}
.lw-panel-table-link:hover{text-decoration:underline}
.lw-panel-table-link--danger{color:#b91c1c;background:none;border:none;padding:0;cursor:pointer;font-family:ui-monospace,monospace;font-size:.8125rem;font-weight:600}
.lw-panel-table-link--danger:hover{text-decoration:underline}
.lw-panel-danger-zone{max-width:32rem;padding:1.25rem 1.5rem;background:#fef2f2;border:1px solid #fecaca;border-radius:.75rem}
.lw-panel-btn--danger{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
.lw-panel-btn--danger:hover:not(.is-disabled){background:#fee2e2;border-color:#fca5a5}
.lw-panel-btn.is-disabled,.lw-panel-btn[aria-disabled=true]{opacity:.55;cursor:not-allowed}
.lw-panel--rt .lw-rt-data-row-actions,
.lw-panel--kelurahan .lw-rt-data-row-actions{display:flex;flex-wrap:wrap;gap:.35rem;align-items:center}
.lw-panel--rt .lw-rt-delete-action-form{display:inline;margin:0}
.lw-panel-table-empty{padding:2rem 1rem;text-align:center;color:var(--lw-text-faint)}
.lw-panel-page-head--row{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem}
.lw-panel-card{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;padding:1.25rem 1.5rem;max-width:42rem}
.lw-panel-card-note{margin:0 0 1rem;font-size:.75rem;color:var(--lw-text-muted)}
.lw-panel-card-title{margin:0;font-size:1.125rem;font-weight:700;color:var(--lw-accent-dark)}
.lw-panel-card-title--spaced{margin-bottom:.75rem}
.lw-panel-card-title--spaced-sm{margin-bottom:.5rem}
.lw-panel-stack{display:flex;flex-direction:column;gap:1rem;min-width:0}
.lw-panel-field--inline{display:flex;align-items:center;gap:.5rem;font-weight:400}
.lw-panel-field--inline input{margin:0}
.lw-panel-muted-note{font-size:.8125rem;color:var(--lw-text-muted);margin:0}
.lw-panel-pre{white-space:pre-wrap}
.lw-panel-snapshot{padding:.75rem;margin-bottom:.75rem;font-size:.8125rem}
.lw-panel-snapshot-title{font-weight:600;color:var(--lw-text);margin:0 0 .5rem;font-size:.8125rem}
.lw-panel-signed-at{color:#065f46;font-weight:600}
.lw-panel-warn-box{font-size:.8125rem;color:#92400e;margin:0;padding:.5rem}
.lw-panel-actions-row{display:flex;flex-wrap:wrap;gap:.5rem}
.lw-panel-dl{margin:1rem 0 0;font-size:.875rem}
.lw-panel-dl-row{display:flex;flex-wrap:wrap;gap:.25rem .5rem;padding:.5rem 0;border-bottom:1px solid var(--lw-border-soft)}
.lw-panel-dl-row:last-child{border-bottom:none}
.lw-panel-dl dt{color:var(--lw-text-muted);font-weight:500;min-width:5rem}
.lw-panel-dl dd{margin:0;color:var(--lw-text-body);flex:1}
.lw-panel-form{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;padding:1.25rem 1.5rem;max-width:32rem}
.lw-panel-form .lw-panel-field{margin-bottom:.875rem}
.lw-panel-form label{display:block;font-size:.8125rem;font-weight:600;color:var(--lw-text-secondary);margin-bottom:.35rem}
.lw-panel-form input,.lw-panel-form select,.lw-panel-form textarea{width:100%;border:1px solid var(--lw-input-border);border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem}
.lw-panel-btn{display:inline-block;padding:.5rem 1rem;font-size:.8125rem;font-weight:600;color:#fff;background:var(--lw-accent);border:none;border-radius:.5rem;text-decoration:none;cursor:pointer;transition:background .15s}
.lw-panel-btn:hover{background:var(--lw-accent-hover)}
.lw-panel-btn--secondary{background:var(--lw-bg-card);color:var(--lw-accent);border:1px solid var(--lw-border-accent-strong)}
.lw-panel-btn--secondary:hover{background:var(--lw-bg-accent-soft)}
.lw-panel-role-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.75rem}
@media(min-width:640px){.lw-panel-role-grid{grid-template-columns:repeat(3,1fr)}}
.lw-panel-role-item{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;padding:1rem}
.lw-panel-role-label{margin:0;font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--lw-text-muted)}
.lw-panel-role-value{margin:.35rem 0 0;font-size:1.25rem;font-weight:800;color:var(--lw-accent-dark)}
.lw-panel-profile{display:grid;gap:1.5rem;margin-bottom:1.5rem}
.lw-panel-profile-block + .lw-panel-profile-block{margin-top:2rem;padding-top:2rem;border-top:1px solid var(--lw-border)}
.lw-panel-profile-hub{display:grid;gap:1.25rem;margin-top:1.5rem}
@media(min-width:768px){.lw-panel-profile-hub{grid-template-columns:repeat(2,minmax(0,1fr))}}
.lw-panel-profile-card{display:flex;flex-direction:column;align-items:center;text-align:center;gap:1rem;padding:1.5rem 1.25rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.875rem;box-shadow:var(--lw-shadow-sm)}
@media(min-width:640px){.lw-panel-profile-card{flex-direction:row;align-items:flex-start;text-align:left;padding:1.5rem}}
.lw-panel-profile-card__photo-link{position:relative;display:block;flex-shrink:0;border-radius:9999px;overflow:hidden;text-decoration:none}
.lw-panel-profile-card__photo-link:hover .lw-panel-profile-card__photo-hint{opacity:1}
.lw-panel-profile-card__photo{width:6rem;height:6rem;border-radius:9999px;object-fit:cover;border:3px solid var(--lw-border-accent);background:var(--lw-bg-accent-soft)}
.lw-panel-profile-card__photo--placeholder{display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:800;color:var(--lw-accent)}
.lw-panel-profile-card__photo-hint{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(4,120,87,.72);color:#fff;font-size:.75rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;opacity:0;transition:opacity .15s}
.lw-panel-profile-card__body{flex:1;min-width:0}
.lw-panel-profile-card__eyebrow{margin:0 0 .35rem;font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-text-faint)}
.lw-panel-profile-card__title{margin:0;font-size:1.125rem;font-weight:800;color:var(--lw-text-strong);line-height:1.35}
.lw-panel-profile-card__meta{margin:.35rem 0 0;font-size:.875rem;color:var(--lw-text-muted);line-height:1.45}
.lw-panel-profile-card__meta--muted{font-size:.8125rem;color:var(--lw-text-faint)}
.lw-panel-profile-card__actions{margin-top:1rem;display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center}
@media(min-width:640px){.lw-panel-profile-card__actions{justify-content:flex-start}}
.lw-panel-btn--sm{padding:.4rem .875rem;font-size:.75rem}
.lw-panel-profile-show-hero{display:flex;flex-direction:column;align-items:center;text-align:center;gap:1rem;padding-bottom:1rem;border-bottom:1px solid var(--lw-border-soft)}
@media(min-width:640px){.lw-panel-profile-show-hero{flex-direction:row;align-items:flex-start;text-align:left;gap:1.25rem}}
.lw-panel-profile-show-hero__photo-wrap{flex-shrink:0}
.lw-panel-profile-show-hero__photo{width:7rem;height:7rem;border-radius:9999px;object-fit:cover;border:3px solid var(--lw-border-accent);background:var(--lw-bg-accent-soft)}
.lw-panel-profile-show-hero__photo--placeholder{display:flex;align-items:center;justify-content:center;width:7rem;height:7rem;border-radius:9999px;border:3px solid var(--lw-border-accent);background:var(--lw-bg-accent-soft);font-size:2rem;font-weight:800;color:var(--lw-accent)}
.lw-panel-profile-show-hero__content{flex:1;min-width:0}
.lw-panel-profile-show-hero__title{margin:.25rem 0 0}
.lw-panel-profile-show-hero__role{margin:.5rem 0 0;font-size:.875rem;color:var(--lw-text-muted)}
.lw-panel-profile-show-text{margin:.5rem 0 0;font-size:.875rem;color:var(--lw-text-body);line-height:1.55}
.lw-panel-profile-show-actions{display:flex;flex-wrap:wrap;gap:.625rem;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--lw-border-soft)}
.lw-panel-page-back{display:inline-block;margin-bottom:0;font-size:.875rem;font-weight:600;color:var(--lw-accent);text-decoration:none}
.lw-panel-page-back:hover{text-decoration:underline}
@media(min-width:768px){.lw-panel-profile{grid-template-columns:minmax(12rem,16rem) 1fr;align-items:start}}
.lw-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.lw-panel-profile-form--grid{display:grid;gap:1.5rem;margin-bottom:0}
@media(min-width:768px){.lw-panel-profile-form--grid{grid-template-columns:minmax(12rem,16rem) 1fr;align-items:start}}
.lw-panel-profile-photo-column{min-width:0}
.lw-panel-profile-fields--grid{min-width:0}
.lw-panel-profile-photo-wrap .lw-panel-table-link--danger{margin-top:.25rem}
.lw-panel-profile-photo-wrap{display:flex;flex-direction:column;align-items:center;gap:.75rem;padding:1.25rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem}
.lw-panel-profile-avatar{width:7rem;height:7rem;border-radius:50%;object-fit:cover;border:3px solid #a7f3d0;background:var(--lw-bg-accent-soft)}
.lw-panel-profile-avatar--placeholder{display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:var(--lw-accent)}
.lw-panel-profile-upload{position:relative;display:inline-block;cursor:pointer}
.lw-panel-profile-file-input{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.lw-panel-profile-upload-label{display:inline-block;padding:.45rem .875rem;font-size:.75rem;font-weight:600;color:var(--lw-accent);background:var(--lw-bg-accent-soft);border:1px solid var(--lw-border-accent-strong);border-radius:.5rem;transition:background .15s}
.lw-panel-profile-upload:hover .lw-panel-profile-upload-label{background:#d1fae5}
.lw-panel-profile-hint{margin:0;font-size:.6875rem;color:var(--lw-text-faint);text-align:center}
.lw-panel-form--wide{max-width:36rem}
.lw-panel-form-grid{display:grid;gap:.875rem 1rem}
.lw-panel-form-grid--2{grid-template-columns:repeat(2,minmax(0,1fr))}
.lw-panel-field--span2{grid-column:1/-1}
.lw-rt-reg-form.lw-panel-form--wide{max-width:100%}
@media(max-width:640px){.lw-panel-form-grid--2{grid-template-columns:1fr}}
.lw-panel-field-hint{margin:.35rem 0 0;font-size:.6875rem;color:var(--lw-text-faint)}
.lw-panel-field-hint--flush{margin:0}
.lw-staff-email-input{display:flex;align-items:stretch;width:100%;max-width:100%}
.lw-staff-email-input__local{flex:1 1 auto;min-width:0;border-top-right-radius:0;border-bottom-right-radius:0;border-right:0}
.lw-staff-email-input__suffix{display:flex;align-items:center;padding:0 .75rem;font-size:.875rem;color:var(--lw-text-muted);background:var(--lw-surface-alt,#f1f5f9);border:1px solid var(--lw-border,#cbd5e1);border-left:0;border-radius:0 .375rem .375rem 0;white-space:nowrap}
.lw-panel-profile-linked{margin:.75rem 0 0;font-size:.8125rem;color:var(--lw-accent)}
.lw-panel-profile-warn{margin:.75rem 0 0;font-size:.8125rem;color:#b45309;background:#fffbeb;border:1px solid #fcd34d;border-radius:.5rem;padding:.5rem .75rem}
.lw-panel-profile-warn--compact{margin:0;padding:.2rem .45rem;font-size:.6875rem;display:inline-block}
.lw-panel-user-link{display:flex;align-items:center;gap:.625rem;text-decoration:none;color:inherit;margin-bottom:.5rem;border-radius:.5rem;padding:.25rem;cursor:pointer;transition:background .15s}
.lw-panel-user-link:hover{background:rgba(255,255,255,.08)}
.lw-panel-user-link--active{background:rgba(255,255,255,.12)}
.lw-panel-user-link:focus-visible{outline:2px solid rgba(255,255,255,.6);outline-offset:2px}
.lw-panel-user-identity{display:flex;align-items:center;gap:.625rem;margin-bottom:.5rem;color:inherit}
.lw-panel-user-avatar{width:2.25rem;height:2.25rem;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.35);flex-shrink:0}
.lw-badge{display:inline-block;padding:.2rem .5rem;font-size:.75rem;font-weight:600;border-radius:.375rem;background:#d1fae5;color:var(--lw-accent-text)}
.lw-badge--amber{background:#fef3c7;color:#92400e}
.lw-badge--blue{background:#dbeafe;color:#1e40af}
.lw-badge--green{background:#d1fae5;color:var(--lw-accent-text)}
.lw-badge--red{background:#fee2e2;color:#991b1b}
.lw-badge--muted{background:var(--lw-bg-subtle);color:var(--lw-text-secondary)}
.lw-panel-topbar-actions{display:flex;align-items:center;gap:.5rem;flex-shrink:0}

@media(min-width:768px){
.lw-panel-backdrop{display:none!important}
.lw-panel-sidebar{position:sticky;top:0;height:100vh;transform:none;width:16rem}
.lw-panel-menu-toggle:checked~.lw-panel-sidebar{transform:none}
.lw-panel-topbar{display:none}
.lw-panel-content{padding:1.5rem 1.75rem 2.5rem}
}

@media(max-height:800px){
.lw-panel-brand{padding:.75rem}
.lw-panel-brand-title{font-size:.9375rem}
.lw-panel-date{margin-top:.375rem;padding:.3rem .45rem;font-size:.75rem}
.lw-panel-nav{padding:.375rem .5rem}
.lw-panel-nav-link{padding:.375rem .5rem;font-size:.8125rem}
.lw-panel-nav-group,.lw-admin-nav-group{margin-bottom:.375rem}
.lw-panel-nav-group-label,.lw-admin-nav-group-label{margin-bottom:.2rem}
.lw-panel-user{padding:.625rem}
.lw-panel-user-name{font-size:.875rem}
.lw-panel-user-avatar{width:2rem;height:2rem}
.lw-panel-nav-icon,.lw-admin-nav-icon{width:1.0625rem;height:1.0625rem}
}

@media(max-height:680px){
.lw-panel-date{margin-top:.25rem;padding:.25rem .4rem;font-size:.6875rem;line-height:1.3}
.lw-panel-date-label{margin-bottom:.05rem}
.lw-panel-brand{padding:.625rem .75rem}
.lw-panel-brand-sub{font-size:.6875rem}
.lw-panel-nav-link{padding:.3rem .45rem;font-size:.8125rem}
.lw-panel-user{padding:.5rem}
.lw-panel-logout-btn{margin-top:.375rem;padding:.4rem .75rem;font-size:.8125rem}
}

/* ——— Panel kelurahan: filter, tabel lebar, ringkasan RT, cetak ——— */
.lw-kel-filter-bar{margin-bottom:1.25rem;padding:1rem 1.125rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem}
.lw-kel-filter-grid{display:grid;gap:.75rem;grid-template-columns:1fr}
@media(min-width:640px){.lw-kel-filter-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:1024px){.lw-kel-filter-grid{grid-template-columns:repeat(4,1fr)}}
.lw-kel-filter-field{display:flex;flex-direction:column;gap:.3rem}
.lw-kel-filter-field label{font-size:.75rem;font-weight:600;color:var(--lw-text-secondary)}
.lw-kel-filter-field input,.lw-kel-filter-field select{width:100%;border:1px solid var(--lw-input-border);border-radius:.5rem;padding:.45rem .65rem;font-size:.8125rem;background:var(--lw-bg-card)}
.lw-kel-filter-actions{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--lw-border-soft)}
.lw-panel-table-wrap--wide{max-height:70vh;overflow:auto;-webkit-overflow-scrolling:touch}
.lw-panel-table-wrap--wide .lw-panel-table{min-width:1600px}
.lw-panel-table--dense th,.lw-panel-table--dense td{padding:.4rem .5rem;font-size:.6875rem;white-space:nowrap}
.lw-panel-table--dense th.lw-panel-th-sticky{position:sticky;top:0;z-index:2;background:var(--lw-bg-muted);box-shadow:0 1px 0 var(--lw-border)}
.lw-kel-rt-summary{margin-bottom:1.25rem}
.lw-kel-rt-summary details{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;margin-bottom:.5rem}
.lw-kel-rt-summary summary{cursor:pointer;padding:.75rem 1rem;font-size:.875rem;font-weight:600;color:var(--lw-accent-dark);list-style:none;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem}
.lw-kel-rt-summary summary::-webkit-details-marker{display:none}
.lw-kel-rt-summary summary::after{content:"+";font-weight:700;color:var(--lw-accent)}
.lw-kel-rt-summary details[open] summary::after{content:"−"}
.lw-kel-rt-summary-body{padding:0 1rem 1rem;font-size:.8125rem;color:var(--lw-text-body)}
.lw-kel-rt-summary-meta{display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:.5rem}
.lw-kel-rt-summary-meta span{color:var(--lw-text-muted)}
.lw-kel-rt-summary-meta strong{color:var(--lw-accent-dark)}
.lw-rt-data-toolbar{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem}
.lw-rt-data-toolbar-actions{display:flex;flex-wrap:wrap;gap:.5rem}
.lw-rt-data-toolbar-filters{display:flex;flex-direction:column;gap:.75rem;flex:1;min-width:min(100%,20rem)}
.lw-rt-data-search{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
.lw-rt-data-search input[type=search]{flex:1;min-width:12rem;padding:.5rem .75rem;border:1px solid var(--lw-border);border-radius:.5rem;font-size:.875rem}
.lw-rt-data-category-tabs{margin-top:0}
.lw-rt-data-residents-table-outer{margin-bottom:0;max-width:100%;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;overflow:hidden;overflow-x:hidden}
.lw-rt-data-residents-table-scroll{overflow-x:hidden;overflow-y:visible;max-width:100%}
.lw-rt-data-residents-table-wrap{margin-bottom:0}
.lw-panel-table.lw-rt-data-residents-table{width:100%!important;min-width:0!important;max-width:100%;table-layout:fixed;border-collapse:collapse}
.lw-panel-table.lw-rt-data-residents-table th,.lw-panel-table.lw-rt-data-residents-table td{padding:.5rem .625rem;min-width:0;vertical-align:middle}
.lw-rt-data-residents-table th,.lw-rt-data-residents-table td{overflow:hidden}
.lw-rt-data-residents-table .lw-rt-data-col-status .lw-badge{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:middle}
.lw-rt-data-residents-table .lw-panel-table-actions{white-space:normal;flex-shrink:1;min-width:0;max-width:100%}
.lw-rt-data-residents-table .lw-panel-btn--sm{padding:.3rem .5rem;font-size:.6875rem}
.lw-rt-data-residents-table tbody tr:hover td{background:var(--lw-bg-accent-soft,#f0fdfa)}
.lw-rt-data-resident-row.is-focused{scroll-margin-top:5rem}
.lw-rt-data-resident-row.is-focused td{background:var(--lw-bg-accent-soft,#f0fdfa);box-shadow:inset 3px 0 0 var(--lw-accent)}
.lw-rt-data-resident-row--kk-start td{border-top:2px solid var(--lw-border-soft,var(--lw-border))}
.lw-rt-data-residents-table .lw-rt-data-row-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.35rem;align-items:center;min-width:0}
.lw-rt-data-residents-table .lw-rt-data-col-kk{width:14%}
.lw-rt-data-residents-table .lw-rt-data-col-name{width:22%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;text-align:left}
.lw-rt-data-residents-table .lw-rt-data-col-nik{width:18%;text-align:left}
.lw-rt-data-residents-table .lw-rt-data-col-ttl{width:16%}
.lw-rt-data-residents-table .lw-rt-data-col-status{width:12%}
.lw-rt-data-residents-table .lw-rt-data-col-actions{width:18%;text-align:right;white-space:normal}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-rt{width:9%;vertical-align:top}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-kk{width:13%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-name{width:18%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-nik{width:16%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-ttl{width:15%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-status{width:11%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-actions{width:18%}
.lw-rt-data-ttl-text{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;line-clamp:2;overflow:hidden;line-height:1.35;word-break:break-word}
.lw-rt-data-col-nik{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lw-rt-data-residents-table .lw-rt-data-col-nik{font-family:ui-monospace,monospace;font-size:.8125rem;word-break:normal}
.lw-rt-data-col-kk{vertical-align:top;overflow:hidden;text-overflow:ellipsis}
.lw-rt-data-kk-number{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
@media(max-width:639px){
.lw-rt-data-residents-table .lw-rt-data-col-ttl{display:none}
.lw-rt-data-residents-table .lw-rt-data-col-kk{width:14%}
.lw-rt-data-residents-table .lw-rt-data-col-name{width:24%}
.lw-rt-data-residents-table .lw-rt-data-col-nik{width:22%}
.lw-rt-data-residents-table .lw-rt-data-col-status{width:12%}
.lw-rt-data-residents-table .lw-rt-data-col-actions{width:24%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-rt{width:10%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-kk{width:12%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-name{width:20%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-nik{width:18%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-status{width:10%}
.lw-rt-data-residents-table--with-rt .lw-rt-data-col-actions{width:22%}
}
.lw-rt-data-kk-number{display:block;font-family:ui-monospace,monospace;font-size:.8125rem;font-weight:600}
.lw-rt-data-kk-number:empty{display:none}
.lw-rt-data-kk-continued{display:block;min-height:1px}
.lw-rt-resident-detail-table{width:100%;margin:0}
.lw-rt-resident-detail-table th,.lw-rt-resident-detail-table td{padding:.45rem .625rem;vertical-align:top;border-bottom:1px solid var(--lw-border-soft)}
.lw-rt-resident-detail-table tr:last-child th,.lw-rt-resident-detail-table tr:last-child td{border-bottom:none}
.lw-rt-resident-detail-table th[scope=row]{width:40%;font-weight:500;color:var(--lw-text-muted);text-align:left}
.lw-rt-resident-detail-table td{color:var(--lw-text-body)}
.lw-rt-resident-detail-section th{background:var(--lw-bg-muted);color:var(--lw-accent-dark);font-size:.6875rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;text-align:left;padding:.5rem .625rem;border-bottom:1px solid var(--lw-border-soft)}
.lw-rt-resident-detail-section+tr th,.lw-rt-resident-detail-section+tr td{border-top:none}
.lw-panel--rt .lw-rt-resident-detail-page .lw-panel-page-head--row{align-items:center}
.lw-panel--rt .lw-rt-resident-detail-page .lw-panel-actions{margin:0;gap:.375rem;align-items:center}
.lw-panel--rt .lw-rt-resident-detail-page .lw-rt-delete-action-form{display:inline-flex}
.lw-panel--rt .lw-rt-resident-detail-page .lw-panel-table-wrap{overflow-x:visible;-webkit-overflow-scrolling:auto}
.lw-panel--rt .lw-rt-resident-detail-page .lw-panel-table{min-width:0}
.lw-panel--rt .lw-rt-resident-edit-page .lw-panel-card--full{max-width:none}
.lw-panel--rt .lw-rt-resident-edit-page .lw-panel-page-head--row{align-items:center}
.lw-panel--rt .lw-rt-resident-edit-page .lw-panel-actions{margin:0;gap:.375rem;align-items:center}
.lw-rt-resident-edit-page .lw-rt-resident-detail-table input,.lw-rt-resident-edit-page .lw-rt-resident-detail-table select,.lw-rt-resident-edit-page .lw-rt-resident-detail-table textarea{width:100%;max-width:100%;padding:.375rem .5rem;border:1px solid var(--lw-border-soft);border-radius:.375rem;font:inherit;color:var(--lw-text-body);background:var(--lw-bg-card)}
.lw-rt-resident-edit-page .lw-rt-resident-detail-table td .lw-panel-field-hint{margin-top:.25rem;font-size:.75rem}
.lw-rt-resident-edit-page .lw-rt-edit-inline-pair{display:flex;flex-wrap:wrap;gap:.5rem}
.lw-rt-resident-edit-page .lw-rt-edit-inline-pair input{flex:1;min-width:8rem}
.lw-rt-resident-edit-page .lw-rt-edit-check{display:inline-flex;align-items:center;gap:.375rem;font-weight:400}
.lw-rt-resident-edit-page .lw-rt-resident-last-updated{margin:.75rem 0 0;font-size:.75rem;color:var(--lw-text-muted)}
.lw-rt-resident-edit-page .lw-panel-table-wrap{overflow-x:visible;-webkit-overflow-scrolling:auto}
.lw-rt-resident-edit-page .lw-panel-table{min-width:0}
.lw-rt-resident-edit-page .lw-rt-resident-detail-table th[scope=row]{width:32%;max-width:14rem}
@media(min-width:1024px){.lw-rt-resident-edit-page .lw-rt-resident-detail-table th[scope=row]{width:28%;max-width:16rem}}
.lw-panel--rt .lw-rt-entity-page .lw-panel-page-head--row{align-items:center}
.lw-panel--rt .lw-rt-entity-page .lw-panel-actions{margin:0;gap:.375rem;align-items:center}
.lw-panel--rt .lw-rt-household-edit-page .lw-panel-card--full{max-width:none}
.lw-panel--rt .lw-rt-household-edit-page .lw-panel-page-head--row{align-items:center}
.lw-rt-household-edit-page .lw-rt-resident-detail-table input,.lw-rt-household-edit-page .lw-rt-resident-detail-table select,.lw-rt-household-edit-page .lw-rt-resident-detail-table textarea{width:100%;max-width:100%;padding:.375rem .5rem;border:1px solid var(--lw-border-soft);border-radius:.375rem;font:inherit;color:var(--lw-text-body);background:var(--lw-bg-card)}
.lw-rt-household-edit-page .lw-rt-resident-detail-table td .lw-panel-field-hint{margin-top:.25rem;font-size:.75rem}
.lw-rt-household-edit-page .lw-panel-table-wrap{overflow-x:visible;-webkit-overflow-scrolling:auto}
.lw-rt-household-edit-page .lw-panel-table{min-width:0}
.lw-rt-household-edit-page .lw-rt-resident-detail-table th[scope=row]{width:32%;max-width:14rem}
.lw-rt-household-edit-page .lw-rt-resident-last-updated{margin:.75rem 0 0;font-size:.75rem;color:var(--lw-text-muted)}
.lw-pre-wrap-block{margin:0;white-space:pre-wrap}
.lw-alert__title{margin:0 0 .25rem;font-weight:600}
.lw-panel-form--sidebar{max-width:none;height:fit-content;margin-bottom:0}
.lw-panel-form--sidebar.lw-panel-form--warn{border-color:#fde68a;background:var(--lw-stat-warn)}
.lw-panel-form--sidebar .lw-panel-btn--block{width:100%}
.lw-panel-form--sidebar .lw-panel-btn--warn{background:#d97706}
.lw-panel-form--sidebar .lw-panel-btn--danger{background:#b91c1c}
.lw-panel-form--in-card .lw-panel-form-actions{margin-top:1rem}
.lw-panel-section-title--clickable{cursor:pointer;margin:0}
.lw-panel-btn--block{width:100%}
.lw-panel-btn--warn{background:#d97706}
.lw-panel-btn--danger{background:#b91c1c}
.lw-panel-form--sidebar.lw-panel-form--danger{border-color:#fecaca;background:#fef2f2}
.lw-rt-delete-modal{position:fixed;inset:0;z-index:200;display:flex;align-items:center;justify-content:center;padding:1rem}
.lw-rt-delete-modal[hidden]{display:none}
.lw-rt-delete-modal__backdrop{position:absolute;inset:0;background:rgba(15,23,42,.45)}
.lw-rt-delete-modal__card{position:relative;z-index:1;width:100%;max-width:28rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;padding:1.25rem;box-shadow:var(--lw-shadow-md)}
.lw-rt-delete-modal-open{overflow:hidden}
.lw-rt-applications-settings-btn{position:relative;display:inline-flex;align-items:center;justify-content:center;gap:.375rem;min-height:2.75rem;padding-inline:.875rem}
.lw-rt-settings-badge{position:absolute;top:.35rem;right:.35rem;width:.5rem;height:.5rem;border-radius:999px;background:#d97706;border:2px solid var(--lw-bg-card,#fff)}
.lw-rt-stamp-settings-modal__card{max-width:32rem}
.lw-rt-delete-trigger{display:inline-flex}
.lw-rt-data-kk-table-wrap{margin-bottom:0}
.lw-rt-data-resident-household-table-wrap{margin-bottom:0}
.lw-panel-table.lw-rt-data-resident-household-table{width:100%!important;min-width:0!important;max-width:100%;table-layout:fixed;border-collapse:collapse}
.lw-panel-table.lw-rt-data-resident-household-table th,.lw-panel-table.lw-rt-data-resident-household-table td{padding:.5rem .625rem;min-width:0;vertical-align:middle}
.lw-rt-data-resident-household-table tbody tr.lw-rt-data-resident-row:hover td{background:var(--lw-bg-accent-soft,#f0fdfa)}
.lw-rt-data-resident-household-table .lw-rt-data-resident-row.is-focused{scroll-margin-top:5rem}
.lw-rt-data-resident-household-table .lw-rt-data-resident-row.is-focused td{background:var(--lw-bg-accent-soft,#f0fdfa);box-shadow:inset 3px 0 0 var(--lw-accent)}
.lw-rt-data-resident-household-table .lw-rt-data-resident-row.is-expanded td{font-weight:500}
.lw-rt-data-resident-household-table .lw-rt-data-col-kk{width:18%}
.lw-rt-data-resident-household-table .lw-rt-data-col-name{width:22%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lw-rt-data-resident-household-table .lw-rt-data-col-nik{width:20%;font-family:ui-monospace,monospace;font-size:.8125rem}
.lw-rt-data-resident-household-table .lw-rt-data-col-status{width:14%}
.lw-rt-data-resident-household-table .lw-rt-data-col-kategori{width:14%}
.lw-rt-data-resident-household-table .lw-rt-data-col-actions{width:12%;text-align:right}
.lw-rt-data-kk-table thead th{position:sticky;top:0;z-index:1;background:var(--lw-bg-card)}
.lw-rt-data-resident-household-table thead th{position:sticky;top:0;z-index:1;background:var(--lw-bg-card)}
.lw-rt-data-kk-row:hover td{background:var(--lw-bg-accent-soft,#f0fdfa)}
.lw-rt-data-kk-row.is-focused{scroll-margin-top:5rem}
.lw-rt-data-kk-row.is-focused td{background:var(--lw-bg-accent-soft,#f0fdfa);box-shadow:inset 3px 0 0 var(--lw-accent)}
.lw-rt-data-kk-row.is-expanded td{font-weight:500}
.lw-rt-data-col-expand{width:2.25rem;padding-left:.5rem;padding-right:.25rem}
.lw-rt-data-expand-btn{display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;padding:0;border:1px solid var(--lw-border);border-radius:.375rem;background:var(--lw-bg-card);color:var(--lw-accent-dark);cursor:pointer;font-size:.875rem;line-height:1;transition:transform .15s ease}
.lw-rt-data-expand-btn:hover{background:var(--lw-bg-accent-soft)}
.lw-rt-data-expand-btn.is-expanded{transform:rotate(180deg)}
.lw-rt-data-kk-detail td{padding:0!important;border-top:none;vertical-align:top}
.lw-rt-data-kk-detail-inner{padding:.75rem 1rem 1rem;background:var(--lw-bg-subtle,#f8fafc);border-top:1px solid var(--lw-border-soft,var(--lw-border))}
.lw-rt-data-kk-dl{display:grid;grid-template-columns:repeat(auto-fill,minmax(10rem,1fr));gap:.5rem 1rem;margin:0 0 1rem;font-size:.8125rem}
.lw-rt-data-kk-dl dt{color:var(--lw-text-muted);font-weight:500}
.lw-rt-data-kk-dl dd{margin:0;color:var(--lw-text-body)}
.lw-rt-data-source-badge{font-size:.625rem;font-weight:600;padding:.15rem .45rem;border-radius:999px;text-transform:uppercase;letter-spacing:.02em;white-space:nowrap}
.lw-rt-data-source-badge--pendataan{background:var(--lw-bg-accent-soft);color:var(--lw-accent-dark)}
.lw-rt-data-source-badge--rt{background:var(--lw-bg-muted,#f1f5f9);color:var(--lw-text-muted)}
.lw-rt-data-member-table-wrap{margin-top:.5rem}
.lw-rt-data-member-table th,.lw-rt-data-member-table td{font-size:.75rem}
.lw-rt-data-member-table tr.is-current-resident td{background:var(--lw-bg-accent-soft,#f0fdfa)}
.lw-rt-unified-kk-card.lw-panel-card--full{padding-top:1.25rem}
.lw-rt-unified-kk-docs{margin-bottom:0}
.lw-rt-unified-kk .lw-rt-surat-readiness-callout{margin-top:0}
.lw-rt-unified-kk-section{margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--lw-border-soft,var(--lw-border))}
.lw-rt-unified-kk-section--docs{margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--lw-border-soft,var(--lw-border))}
.lw-rt-unified-kk-section-title{margin:0 0 .75rem;font-size:.9375rem;font-weight:700;color:var(--lw-text-body)}
.lw-panel-section-head .lw-rt-unified-kk-section-title{margin:0}
.lw-rt-unified-kk-section--member-detail .lw-rt-unified-member-profile{margin-top:0}
.lw-rt-household-members-panel{margin:0}
.lw-rt-surat-readiness-callout+.lw-rt-household-members-panel{margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--lw-border-soft,var(--lw-border))}
.lw-rt-household-members-title{margin:0 0 1rem;font-size:1rem;font-weight:700;color:var(--lw-text-body)}
.lw-rt-household-members-table-wrap{margin:0;border-radius:.5rem;overflow:hidden;border:1px solid var(--lw-border-soft,var(--lw-border))}
.lw-rt-household-members-table{margin:0}
.lw-rt-household-members-table thead{background:var(--lw-bg-accent-soft,#ecfdf5)}
.lw-rt-household-members-table th,.lw-rt-household-members-table td{font-size:.8125rem;padding:.625rem .75rem}
.lw-rt-household-members-table th{font-weight:600;color:var(--lw-text-body)}
.lw-rt-household-members-table tr.is-current-resident td{background:var(--lw-bg-accent-soft,#f0fdfa)}
.lw-rt-household-members-kk-badge{font-size:.625rem;margin-left:.25rem;vertical-align:middle}
.lw-rt-household-members-col-no{width:2.5rem;text-align:center;color:var(--lw-text-muted)}
.lw-rt-household-members-col-kk{width:16%;font-family:ui-monospace,monospace;font-size:.75rem;white-space:nowrap}
.lw-rt-household-members-col-actions{width:1%;white-space:nowrap;text-align:right;vertical-align:middle}
.lw-rt-household-members-pending-badge{font-size:.625rem;margin-left:.25rem;vertical-align:middle}
.lw-rt-household-members-footer{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;margin-top:.875rem}
.lw-rt-household-members-summary{margin:0;font-size:.8125rem;color:var(--lw-text-muted)}
.lw-rt-household-members-pagination{display:flex;align-items:center;gap:.25rem}
.lw-rt-household-members-page-btn{min-width:2rem;height:2rem;padding:0 .5rem;border:1px solid var(--lw-border-soft,var(--lw-border));border-radius:999px;background:var(--lw-bg-card);font-size:.8125rem;color:var(--lw-text-body);cursor:pointer;line-height:1}
.lw-rt-household-members-page-btn:hover:not(:disabled){border-color:var(--lw-accent);color:var(--lw-accent-dark)}
.lw-rt-household-members-page-btn.is-active{background:var(--lw-accent);border-color:var(--lw-accent);color:#fff;font-weight:600}
.lw-rt-household-members-page-btn:disabled{opacity:.4;cursor:not-allowed}
.lw-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.lw-rt-data-kk-table .lw-rt-data-col-actions,.lw-rt-data-member-table .lw-rt-data-col-actions{width:1%;white-space:normal;text-align:right;vertical-align:middle;overflow:visible}
.lw-rt-data-kk-table .lw-rt-data-row-actions,.lw-rt-data-member-table .lw-rt-data-row-actions{flex-wrap:wrap;justify-content:flex-end}
.lw-rt-data-active-filters{margin:0 0 .75rem;font-size:.8125rem;color:var(--lw-text-muted)}
.lw-rt-analytics{margin:0 0 1.25rem}
.lw-rt-analytics-grid{display:grid;grid-template-columns:1fr;gap:.875rem}
@media(min-width:900px){.lw-rt-analytics-grid{grid-template-columns:repeat(3,1fr)}}
.lw-rt-analytics-card{background:#fff;border:1px solid #cbd5e1;border-radius:.75rem;padding:1.125rem 1.25rem;display:flex;flex-direction:column;min-height:14rem}
.lw-rt-analytics-card-title{margin:0 0 1rem;font-size:.9375rem;font-weight:700;color:#0f172a}
.lw-rt-analytics-chart{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.75rem}
.lw-rt-analytics-chart--empty{opacity:.85}
.lw-rt-analytics-empty-text{margin:0;font-size:.75rem;color:var(--lw-text-muted);text-align:center}
.lw-rt-analytics-empty-text--partial{max-width:16rem;line-height:1.45}
.lw-rt-analytics-partial-note{margin:.25rem 0 0;font-size:.6875rem;line-height:1.4;color:var(--lw-text-muted);text-align:center;max-width:16rem}
.lw-rt-analytics-summary{margin:0;font-size:.8125rem;font-weight:600;color:var(--lw-text-secondary);text-align:center}
.lw-rt-analytics-pie{width:9rem;height:9rem;border-radius:9999px;flex-shrink:0;border:2px solid #cbd5e1}
.lw-rt-analytics-pie--population{background:conic-gradient(#10b981 0 calc(var(--pie-classified-end,0) * 1%),#e2e8f0 calc(var(--pie-classified-end,0) * 1%) 100%)}
.lw-rt-analytics-pie--empty{background:#e2e8f0}
.lw-rt-analytics-bars{display:flex;align-items:flex-end;justify-content:center;gap:.5rem;width:100%;height:9rem;padding:0 .25rem}
.lw-rt-analytics-bars--empty .lw-rt-analytics-bar{height:12%!important;background:#e2e8f0}
.lw-rt-analytics-bar-col{display:flex;flex-direction:column;align-items:center;justify-content:flex-end;flex:1;max-width:2.5rem;height:100%}
.lw-rt-analytics-bar{width:100%;min-height:.5rem;background:linear-gradient(180deg,#34d399 0%,#10b981 100%);border-radius:.25rem .25rem 0 0;transition:height .2s ease}
.lw-rt-analytics-bar--empty{background:#e2e8f0}
.lw-rt-analytics-bar-label{margin-top:.35rem;font-size:.625rem;font-weight:600;color:var(--lw-text-muted);text-align:center;line-height:1.2}
.lw-rt-analytics-donut-wrap{position:relative;width:9rem;height:9rem;flex-shrink:0}
.lw-rt-analytics-donut{position:absolute;inset:0;width:100%;height:100%;border-radius:9999px;background:conic-gradient(#059669 0 calc(var(--donut-male-end,0) * 1%),#38bdf8 calc(var(--donut-male-end,0) * 1%) 100%);border:2px solid #cbd5e1}
.lw-rt-analytics-donut--empty{background:#e2e8f0}
.lw-rt-analytics-chart>.lw-rt-analytics-donut--empty{position:static;width:9rem;height:9rem;flex-shrink:0}
.lw-rt-analytics-donut:not(.lw-rt-analytics-donut--empty)::after{content:"";position:absolute;inset:22%;border-radius:9999px;background:#fff}
.lw-rt-analytics-donut-center{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:.8125rem;font-weight:700;color:#0f172a;z-index:1;pointer-events:none}
.lw-rt-analytics-legend{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:.4rem;width:100%}
.lw-rt-analytics-legend-item{display:flex;align-items:center;gap:.5rem;font-size:.75rem;color:var(--lw-text-secondary)}
.lw-rt-analytics-swatch{flex-shrink:0;width:.75rem;height:.75rem;border-radius:.2rem}
.lw-rt-analytics-swatch--green{background:#10b981}
.lw-rt-analytics-swatch--gray{background:#e2e8f0;border:1px solid #cbd5e1}
.lw-rt-analytics-swatch--male{background:#059669}
.lw-rt-analytics-swatch--female{background:#38bdf8}
.lw-rt-monograph-wrap{background:#fff;border:1px solid #94a3b8;border-radius:.75rem;overflow-x:auto;-webkit-overflow-scrolling:touch}
.lw-rt-monograph-table{width:100%;min-width:52rem;border-collapse:collapse;font-size:.875rem;background:#fff}
.lw-panel--rt .lw-rt-monograph-table th,.lw-panel--rt .lw-rt-monograph-table td,.lw-panel--admin .lw-rt-monograph-table th,.lw-panel--admin .lw-rt-monograph-table td{border:1px solid #cbd5e1;padding:.6rem .75rem;text-align:center;vertical-align:middle;min-height:2.5rem;line-height:1.35}
.lw-panel--rt .lw-rt-monograph-table thead th,.lw-panel--admin .lw-rt-monograph-table thead th{background:#7dd3fc;color:#0f172a;font-weight:700;border-color:#cbd5e1}
.lw-panel--rt .lw-rt-monograph-table tbody td,.lw-panel--rt .lw-rt-monograph-table tbody th[scope=row],.lw-panel--admin .lw-rt-monograph-table tbody td,.lw-panel--admin .lw-rt-monograph-table tbody th[scope=row]{background:#fff}
.lw-panel--rt .lw-rt-monograph-table tbody th[scope=row],.lw-panel--admin .lw-rt-monograph-table tbody th[scope=row]{font-weight:700;color:#0f172a}
.lw-panel--rt .lw-rt-monograph-table tfoot th,.lw-panel--rt .lw-rt-monograph-table tfoot td,.lw-panel--admin .lw-rt-monograph-table tfoot th,.lw-panel--admin .lw-rt-monograph-table tfoot td{background:#fff;color:#0f172a;font-weight:700;border-color:#cbd5e1}
.lw-panel--rt .lw-rt-monograph-table .lw-rt-monograph-col-rt,.lw-panel--admin .lw-rt-monograph-table .lw-rt-monograph-col-rt{min-width:3.25rem}
.lw-panel--rt .lw-rt-monograph-table .lw-rt-monograph-col-total,.lw-panel--admin .lw-rt-monograph-table .lw-rt-monograph-col-total{min-width:4rem}
.lw-rt-monograph{margin:0 0 1.5rem}
.lw-admin-population-analytics{margin-top:1.5rem}
.lw-admin-population-analytics .lw-rt-analytics{margin-bottom:0}
.lw-admin-population-monograph{margin-top:1.5rem}
.lw-admin-population-monograph .lw-rt-dashboard-monograph-head{margin-bottom:.75rem}
.lw-rt-monograph-table tr.is-highlighted td,.lw-rt-monograph-table tr.is-highlighted th[scope=row]{background:#fff;box-shadow:inset 0 0 0 2px #34d399}
.lw-panel--rt .lw-rt-dashboard-page{display:flex;flex-direction:column;gap:1.25rem}
.lw-panel--rt .lw-rt-dashboard-stats{margin-bottom:0}
.lw-panel--rt .lw-rt-dashboard-quick{margin-bottom:0}
.lw-panel--rt .lw-rt-dashboard-priorities{margin:0}
.lw-panel--rt .lw-rt-dashboard-monograph{margin:0}
.lw-panel--rt .lw-rt-dashboard-monograph-head{margin-bottom:.75rem}
.lw-panel--rt .lw-rt-dashboard-page .lw-rt-analytics{margin-bottom:0}
.lw-panel--rt .lw-rt-dashboard-page .lw-rt-dash-activity{margin-top:0}
.lw-rt-priority-list{display:flex;flex-direction:column;gap:.5rem;margin:0;padding:0;list-style:none}
.lw-rt-priority-item{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;padding:.75rem 1rem;border-radius:.625rem;font-size:.8125rem;border:1px solid var(--lw-border-soft);background:var(--lw-bg-card)}
.lw-rt-priority-item--warn{border-color:#fcd34d;background:var(--lw-stat-warn);color:#92400e}
.lw-rt-priority-item--danger{border-color:#fca5a5;background:#fef2f2;color:#991b1b}
.lw-rt-priority-item--info{border-color:#93c5fd;background:#eff6ff;color:#1e40af}
.lw-rt-priority-item__body{min-width:0;flex:1}
.lw-rt-priority-item__label{margin:0;font-weight:600;line-height:1.4}
.lw-rt-priority-item__meta{margin:.25rem 0 0;font-size:.75rem;opacity:.9;line-height:1.45}
.lw-rt-priority-item__link{font-weight:600;text-decoration:none;white-space:nowrap;color:inherit}
.lw-rt-priority-item__link:hover{text-decoration:underline}
.lw-rt-priority-item--warn .lw-rt-priority-item__link{color:#b45309}
.lw-rt-priority-item--danger .lw-rt-priority-item__link{color:#b91c1c}
.lw-rt-priority-item--info .lw-rt-priority-item__link{color:#1d4ed8}
.lw-rt-monograph-wrap--compact .lw-rt-monograph-table{min-width:36rem}
.lw-rt-dash-activity{margin-top:1rem}
.lw-rt-doc-section{margin:1rem 0 0;padding-top:1rem;border-top:1px solid var(--lw-border-soft)}
.lw-rt-doc-section--compact .lw-panel-section-title{font-size:.875rem;margin-bottom:.5rem}
.lw-rt-doc-empty{margin:0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.5}
.lw-rt-doc-empty-alert{margin:0}
.lw-rt-doc-verify-banner{margin-bottom:.75rem}
.lw-rt-doc-verify-banner-text{margin:0 0 .35rem;font-size:.8125rem;line-height:1.45}
.lw-rt-doc-verify-banner-notes{margin:0 0 .5rem;font-size:.75rem;color:var(--lw-text-muted);line-height:1.4}
.lw-rt-doc-collapsible{margin-top:.25rem;border:1px solid var(--lw-border-soft);border-radius:.625rem;background:var(--lw-bg-card)}
.lw-rt-doc-collapsible-summary{cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:.5rem;font-size:.875rem;font-weight:600;color:var(--lw-accent-dark);padding:.65rem .85rem;list-style:none}
.lw-rt-doc-collapsible-summary::-webkit-details-marker{display:none}
.lw-rt-doc-collapsible-summary::after{content:"";flex-shrink:0;width:.45rem;height:.45rem;border-right:2px solid currentColor;border-bottom:2px solid currentColor;transform:rotate(45deg);margin-top:-.15rem;transition:transform .15s ease}
.lw-rt-doc-collapsible[open] .lw-rt-doc-collapsible-summary::after{transform:rotate(-135deg);margin-top:.15rem}
.lw-rt-doc-collapsible[open] .lw-rt-doc-collapsible-summary{border-bottom:1px solid var(--lw-border-soft)}
.lw-rt-doc-collapsible .lw-rt-doc-grid{padding:.75rem .85rem .85rem;margin-top:0}
.lw-rt-doc-grid{display:grid;gap:1rem;margin-top:.5rem}
.lw-rt-doc-grid--compact{grid-template-columns:repeat(auto-fill,minmax(10.5rem,1fr))}
.lw-rt-doc-grid--full{grid-template-columns:repeat(auto-fill,minmax(12rem,1fr))}
@media(min-width:640px){.lw-rt-doc-grid--compact{grid-template-columns:repeat(auto-fill,minmax(12.5rem,1fr))}}
.lw-rt-doc-card{display:flex;flex-direction:column;gap:.5rem;height:100%;padding:.75rem;border:1px solid var(--lw-border-soft);border-radius:.625rem;background:var(--lw-bg-card);box-shadow:0 1px 2px rgba(15,23,42,.04);min-width:0}
.lw-rt-doc-card--kk{border-left:3px solid #34d399}
.lw-rt-doc-card--ktp{border-left:3px solid #60a5fa}
.lw-rt-doc-card--lampiran{border-left:3px solid var(--lw-border)}
.lw-rt-doc-card-head{display:flex;align-items:center;justify-content:space-between;gap:.5rem;min-width:0}
.lw-rt-doc-card-badge{display:inline-flex;align-items:center;padding:.15rem .5rem;font-size:.6875rem;font-weight:700;line-height:1.3;letter-spacing:.03em;border-radius:999px}
.lw-rt-doc-card--kk .lw-rt-doc-card-badge{color:#047857;background:#d1fae5}
.lw-rt-doc-card--ktp .lw-rt-doc-card-badge{color:#1d4ed8;background:#dbeafe}
.lw-rt-doc-card--lampiran .lw-rt-doc-card-badge{color:var(--lw-text-secondary);background:var(--lw-bg-muted)}
.lw-rt-doc-card-date{margin:0;font-size:.6875rem;color:var(--lw-text-muted);white-space:nowrap}
.lw-rt-doc-card-media{aspect-ratio:4/3;border-radius:.375rem;overflow:hidden;background:var(--lw-bg-subtle);border:1px solid var(--lw-border-soft)}
.lw-rt-doc-card-preview{display:block;width:100%;height:100%;padding:0;border:none;background:none;cursor:zoom-in}
.lw-rt-doc-card-thumb{display:block;width:100%;height:100%;object-fit:cover}
.lw-rt-doc-card-thumb--full{object-fit:contain;background:#fff}
.lw-rt-doc-card-pdf{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.35rem;height:100%;padding:.5rem;text-align:center}
.lw-rt-doc-card-name{margin:0;font-size:.8125rem;font-weight:600;color:var(--lw-text-strong);line-height:1.35;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lw-rt-doc-card-pdf-badge{display:inline-block;padding:.2rem .5rem;font-size:.625rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#991b1b;background:#fee2e2;border-radius:.25rem}
.lw-rt-doc-card-pdf-hint{margin:0;font-size:.6875rem;color:var(--lw-text-muted);line-height:1.35}
.lw-rt-doc-card-actions{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:auto;padding-top:.15rem}
.lw-rt-doc-card-actions .lw-panel-btn{flex:1;min-width:0;min-height:2rem;justify-content:center;text-align:center}
.lw-rt-edit-doc-existing{margin-bottom:1rem}
.lw-rt-edit-doc-item{display:flex;flex-direction:column;height:100%}
.lw-rt-edit-doc-item .lw-rt-doc-card{flex:1}
.lw-rt-edit-doc-remove{display:flex;align-items:center;gap:.4rem;margin-top:.5rem;padding:.45rem .6rem;font-size:.75rem;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;border-radius:.375rem;cursor:pointer}
.lw-rt-edit-doc-remove input{accent-color:#dc2626;margin:0}
.lw-rt-edit-doc-upload{margin-top:1rem;padding-top:1rem;border-top:1px solid var(--lw-border-soft)}
.lw-rt-edit-doc-upload-title{margin:0 0 .75rem;font-size:.8125rem;font-weight:600;color:var(--lw-accent-dark)}
.lw-rt-doc-modal{position:fixed;inset:0;z-index:80;display:flex;align-items:center;justify-content:center;padding:1rem}
.lw-rt-doc-modal[hidden]{display:none}
.lw-rt-doc-modal-backdrop{position:absolute;inset:0;background:rgba(2,6,23,.68)}
.lw-rt-doc-modal-dialog{position:relative;z-index:1;width:min(92vw,56rem);max-height:90vh;overflow:auto;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;padding:1rem;box-shadow:0 12px 32px rgba(15,23,42,.25)}
.lw-rt-doc-modal-close{position:absolute;top:.5rem;right:.65rem;border:none;background:transparent;font-size:1.5rem;line-height:1;cursor:pointer;color:var(--lw-text-secondary)}
.lw-rt-doc-modal-img{display:block;width:100%;height:auto;max-height:72vh;object-fit:contain;border-radius:.5rem;border:1px solid var(--lw-border-soft);background:var(--lw-bg-subtle)}
.lw-rt-doc-modal-title{margin:.75rem 0 .15rem;font-size:.9375rem;font-weight:700;color:var(--lw-text-strong)}
.lw-rt-doc-modal-meta{margin:0;font-size:.75rem;color:var(--lw-text-muted)}
body.lw-rt-doc-modal-open{overflow:hidden}
.lw-rt-doc-chip{display:inline-block;margin-top:.25rem;padding:.1rem .45rem;font-size:.625rem;font-weight:600;line-height:1.3;color:var(--lw-text-secondary);background:var(--lw-bg-muted);border:1px solid var(--lw-border-soft);border-radius:999px;vertical-align:middle}
.lw-rt-doc-chip--pending{color:#92400e;background:#fef3c7;border-color:#fcd34d}
.lw-rt-surat-readiness{display:inline-block;margin-left:.35rem;padding:.1rem .45rem;font-size:.625rem;font-weight:600;line-height:1.3;border-radius:999px;vertical-align:middle;border:1px solid transparent}
.lw-rt-surat-readiness--ready{color:#166534;background:#dcfce7;border-color:#86efac}
.lw-rt-surat-readiness--missing{color:#92400e;background:#fef3c7;border-color:#fcd34d}
.lw-rt-surat-readiness--failed{color:#991b1b;background:#fee2e2;border-color:#fca5a5}
.lw-rt-surat-readiness-callout{margin:0 0 1rem;padding:.75rem 1rem;border-radius:.5rem;border:1px solid transparent}
.lw-rt-surat-readiness-callout__title{margin:0 0 .35rem;font-size:.8125rem;font-weight:700}
.lw-rt-surat-readiness-callout__text{margin:0;font-size:.8125rem;line-height:1.45}
.lw-rt-surat-readiness-callout__detail{margin:.35rem 0 0;font-size:.8125rem;line-height:1.45;opacity:.95}
.lw-rt-surat-readiness-callout__actions{margin:.5rem 0 0;display:flex;flex-wrap:wrap;align-items:center;gap:.5rem .75rem;font-size:.8125rem}
.lw-rt-surat-readiness-callout__sync-form{margin:0}
.lw-rt-surat-readiness-callout__action{margin:.5rem 0 0;font-size:.8125rem}
.lw-rt-surat-readiness-callout.lw-rt-surat-readiness--missing{background:#fffbeb;border-color:#fcd34d;color:#78350f}
.lw-rt-surat-readiness-callout.lw-rt-surat-readiness--failed{background:#fef2f2;border-color:#fca5a5;color:#7f1d1d}
.lw-rt-data-kk-head-cell{display:flex;flex-direction:column;align-items:flex-start;gap:.15rem}
.lw-rt-data-kk-head-cell .lw-rt-surat-readiness{margin-left:0}
@media(max-width:900px){.lw-rt-data-col-address{display:none}}
.lw-rt-reg-members-toolbar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:1rem}
.lw-rt-reg-members{display:flex;flex-direction:column;gap:1rem}
.lw-rt-reg-member{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;padding:1rem 1.125rem}
.lw-rt-reg-member-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:1rem}
.lw-rt-reg-member-title{margin:0;font-size:.9375rem;font-weight:600;color:var(--lw-accent-dark)}
.lw-panel-form--nested .lw-panel-field{margin-bottom:.75rem}
.lw-rt-reg-form{width:100%;max-width:100%;box-sizing:border-box}
.lw-rt-reg-form .lw-panel-form-fieldset{margin-bottom:1.5rem}
.lw-rt-reg-form .lw-panel-form-fieldset:last-of-type{margin-bottom:0}
.lw-rt-reg-form .lw-panel-form-fieldset+.lw-panel-form-fieldset{padding-top:1.25rem;border-top:1px solid var(--lw-border-soft)}
.lw-rt-reg-form .lw-household-recap-fields--panel{display:contents}
.lw-rt-reg-form .lw-panel-field-hint.lw-panel-field--span2{margin:0;grid-column:1/-1}
.lw-rt-reg-form .lw-panel-form-grid--labeled>.lw-panel-field{margin-bottom:0}
.lw-rt-reg-form.lw-panel-form--labeled>.lw-panel-field{margin-bottom:0}
.lw-rt-reg-form .lw-panel-field--span2 .lw-panel-check{margin:0}
@media(min-width:768px){.lw-rt-reg-form{--lw-form-label-col:14.5rem}}
.lw-rt-reg-member .lw-panel-field:last-child{margin-bottom:0}
.lw-rt-reg-member .lw-panel-form-grid .lw-panel-field{margin-bottom:0}
.lw-panel--rt .lw-rt-reg-form .lw-panel-form-grid--labeled>.lw-panel-field,
.lw-panel--rt .lw-rt-reg-form.lw-panel-form--labeled .lw-panel-field{display:flex;flex-direction:column;align-items:flex-start;gap:.35rem;margin-bottom:0;width:100%;min-width:0}
.lw-panel--rt .lw-rt-reg-form .lw-panel-field-label{display:inline-block;width:fit-content;max-width:100%;align-self:flex-start;margin-bottom:0}
.lw-rt-reg-form .lw-panel-field-input,
.lw-rt-reg-form .lw-panel-field select,
.lw-rt-reg-form .lw-panel-field textarea,
.lw-rt-reg-form .lw-panel-field input[type=file]{width:100%;align-self:stretch;box-sizing:border-box}
.lw-rt-reg-form .lw-panel-field>.lw-panel-field-hint,
.lw-rt-reg-form .lw-panel-field>.lw-form-error{width:100%;align-self:stretch}
.lw-rt-reg-form .lw-panel-form-legend,
.lw-rt-reg-form>.lw-panel-field-hint,
.lw-rt-reg-form .lw-panel-field-hint.lw-panel-field--span2,
.lw-rt-reg-form .lw-rt-reg-member-title{width:100%}
.lw-rt-reg-form .lw-panel-check{display:flex;align-items:flex-start;gap:.5rem;width:auto;max-width:100%;min-width:0;font-size:.8125rem;color:var(--lw-text-secondary);cursor:pointer;line-height:1.45}
.lw-rt-reg-form .lw-panel-check input{flex-shrink:0;margin-top:.15rem;accent-color:var(--lw-accent)}
.lw-rt-reg-member .lw-panel-form-grid--labeled{grid-template-columns:repeat(2,minmax(0,1fr));gap:.875rem 1rem}
.lw-rt-reg-member .lw-panel-form-grid--labeled>.lw-panel-field--span2{grid-column:1/-1}
.lw-rt-reg-form .lw-rt-reg-form__check-row{margin-top:.125rem}
.lw-rt-reg-form .lw-panel-form-actions .lw-panel-btn{min-height:2.75rem}
@media(max-width:639px){
.lw-rt-reg-member .lw-panel-form-grid--labeled{grid-template-columns:1fr}
.lw-rt-reg-form.lw-panel-form{padding:1rem}
.lw-rt-reg-members-toolbar{flex-direction:column;align-items:stretch}
.lw-rt-reg-members-toolbar .lw-panel-btn{width:100%;text-align:center}
.lw-rt-reg-form .lw-panel-form-actions{flex-direction:column}
.lw-rt-reg-form .lw-panel-form-actions .lw-panel-btn{width:100%;text-align:center;justify-content:center}
.lw-rt-reg-form input[type=file]{font-size:.8125rem}
}
.lw-kel-pop-warn{color:var(--lw-warn-text,#b45309)}
.lw-kel-pop-toolbar{margin-bottom:1.25rem;padding:1rem 1.125rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem}
.lw-kel-pop-toolbar .lw-kel-filter-bar{margin-bottom:0;padding:0;border:none;background:transparent}
.lw-kel-pop-toolbar-head{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1rem}
.lw-kel-pop-toolbar-head .lw-panel-page-head{margin-bottom:0;flex:1;min-width:12rem}
.lw-kel-pop-toolbar-actions{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
.lw-kel-pop-context{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem 1rem;margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--lw-border-soft);font-size:.8125rem}
.lw-kel-pop-context--stats{border-top:none;margin-top:0;padding-top:0}
.lw-kel-pop-context-chip{font-weight:600;color:var(--lw-accent-dark);background:var(--lw-bg-accent-soft);padding:.25rem .65rem;border-radius:999px}
.lw-kel-pop-context-stats{color:var(--lw-text-muted)}
.lw-kel-pop-summary{margin-bottom:1.25rem}
.lw-kel-pop-summary-table th,.lw-kel-pop-summary-table td{font-size:.8125rem}
.lw-panel-page-head-actions{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
.lw-kel-pop-table-toolbar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.75rem}
.lw-kel-pop-table-toggles{display:flex;flex-wrap:wrap;gap:1rem}
.lw-kel-pop-toggle{display:inline-flex;align-items:center;gap:.4rem;font-size:.8125rem;color:var(--lw-text-secondary);cursor:pointer;user-select:none}
.lw-kel-pop-toggle input{accent-color:var(--lw-accent)}
.lw-kel-pop-range{margin:0 0 .75rem;font-size:.8125rem;color:var(--lw-text-muted)}
.lw-panel-table--population th,.lw-panel-table--population td{padding:.45rem .55rem;font-size:.8125rem;white-space:nowrap;vertical-align:middle}
.lw-panel-table--population .lw-kel-pop-age-col{font-size:.6875rem;padding:.35rem .45rem}
.lw-kel-pop-age-group{text-align:center;font-size:.6875rem}
.lw-kel-pop-age-unclassified{font-size:.6875rem;max-width:4.5rem;white-space:normal;line-height:1.2}
.lw-kel-pop-badge{display:inline-block;margin:0 .15rem .15rem 0;padding:.1rem .35rem;border-radius:.25rem;font-size:.625rem;font-weight:600;background:#fef3c7;color:#92400e;vertical-align:middle;cursor:help}
.lw-kel-pop-badge--recap{background:#fee2e2;color:#991b1b}
.lw-kel-pop-status-cell{max-width:6rem;white-space:normal;line-height:1.35}
.lw-kel-pop-address-cell{max-width:14rem;white-space:normal;line-height:1.35}
.lw-kel-pop-ok{color:var(--lw-text-muted)}
.lw-kel-pop-subtotal td,.lw-kel-pop-subtotal th{background:var(--lw-bg-muted);border-top:2px solid var(--lw-border)}
.lw-panel-th-sticky-left{position:sticky;z-index:4;background:var(--lw-bg-card);box-shadow:1px 0 0 var(--lw-border)}
.lw-panel-table--population thead .lw-panel-th-sticky-left{z-index:5;background:var(--lw-bg-muted)}
.lw-kel-pop-col-rt{left:0;min-width:4.5rem}
.lw-kel-pop-col-no{left:0;min-width:2.5rem;text-align:center}
.lw-kel-pop-col-name{left:2.5rem;min-width:8rem;max-width:12rem;white-space:normal;box-shadow:2px 0 4px -2px rgba(0,0,0,.08)}
.lw-kel-pop-table:not(.lw-kel-pop-table--single-rt) .lw-kel-pop-col-no{left:4.5rem}
.lw-kel-pop-table:not(.lw-kel-pop-table--single-rt) .lw-kel-pop-col-name{left:7rem}
.lw-kel-pop-table:not(.lw-kel-pop-detail-visible):not(.lw-printing) .lw-kel-pop-detail-col:not(.lw-kel-pop-detail-col--rt-mode){display:none}
.lw-kel-pop-table.lw-kel-pop-detail-visible .lw-kel-pop-detail-col,.lw-kel-pop-table--single-rt .lw-kel-pop-detail-col--rt-mode{display:table-cell}
.lw-kel-pop-table:not(.lw-kel-pop-age-visible):not(.lw-printing) .lw-kel-pop-age-col{display:none}
.lw-kel-pop-table.lw-kel-pop-age-visible .lw-panel-table--population{min-width:1800px}
.lw-panel-table-wrap--wide .lw-kel-pop-table:not(.lw-kel-pop-age-visible) .lw-panel-table--population{min-width:720px}
.lw-kel-pop-table--single-rt:not(.lw-kel-pop-age-visible) .lw-panel-table--population{min-width:880px}
.lw-panel-page-head--actions{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem}
.lw-panel-page-head--actions .lw-panel-page-head{margin-bottom:0;flex:1;min-width:12rem}
.lw-panel-doc-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.5rem}
.lw-panel-doc-item{padding:.75rem 1rem;border:1px solid var(--lw-border-soft);border-radius:.5rem;background:var(--lw-bg-surface-muted)}
.lw-panel-doc-item-title{margin:0;font-weight:600;font-size:.875rem;color:var(--lw-text-strong)}
.lw-panel-doc-item-meta{margin:.2rem 0 .5rem;font-size:.75rem;color:var(--lw-text-muted)}
.lw-panel-doc-item-actions{display:flex;flex-wrap:wrap;gap:.75rem}
.lw-panel-grid-2{display:grid;gap:1.25rem}
@media(min-width:1024px){.lw-panel-grid-2{grid-template-columns:1fr 1fr}}
.lw-letter-compose-page{margin-bottom:1.5rem}
.lw-letter-compose-grid{display:grid;gap:1.25rem;align-items:start;max-width:none;grid-template-columns:minmax(0,1fr)}
.lw-letter-compose-editor{max-height:none;overflow:visible;padding-right:0}
.lw-letter-compose-card{padding:1.25rem 1.5rem}
.lw-panel-dl--compact .lw-panel-dl-row{padding:.35rem 0}
.lw-pre-wrap{white-space:pre-wrap}
.lw-letter-compose-form-section{padding-top:1.25rem}
.lw-letter-compose-lead{margin-top:.35rem;margin-bottom:1rem}
.lw-panel-form--in-card{background:transparent;border:0;padding:0;max-width:none}
.lw-letter-fieldset{margin:0 0 1.25rem;padding:0;border:0}
.lw-letter-fieldset--signature{border-top:1px solid var(--lw-border-soft);padding-top:1rem}
.lw-letter-signature-legend{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem;width:100%;padding:0;margin-bottom:.5rem}
.lw-letter-signature-pad{border:2px dashed var(--lw-border-accent-strong);border-radius:.5rem;background:#fff;width:100%;max-width:20rem}
.lw-letter-signature-canvas{display:block;width:100%;height:120px}
.lw-letter-signature-clear{margin-top:.5rem;background:none;border:none;padding:0;cursor:pointer;font-size:.8125rem}
.lw-face-capture{display:grid;gap:.75rem;justify-items:center}
.lw-face-capture__frame{position:relative;width:16rem;height:16rem;margin:0 auto}
.lw-face-capture__switch-btn{position:absolute;top:.5rem;right:.5rem;z-index:2;padding:.35rem .6rem;border:0;border-radius:999px;background:rgba(0,0,0,.65);color:#fff;font-size:.75rem;font-weight:600;line-height:1.2;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.25)}
.lw-face-capture__switch-btn:hover{background:rgba(0,0,0,.8)}
.lw-face-capture__video.is-mirrored{transform:scaleX(-1)}
.lw-face-capture__preview-wrap{position:relative;width:100%;height:100%;border-radius:50%;overflow:hidden;background:#111;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(0,0,0,.18)}
.lw-face-capture__preview-wrap.is-placeholder{border:2px dashed var(--lw-border-accent-strong)}
.lw-face-capture__preview-wrap:not(.is-placeholder){border:3px solid var(--lw-border-accent-strong)}
.lw-face-capture__preview-wrap.is-ready{border-color:#16a34a;box-shadow:0 0 0 4px rgba(22,163,74,.2),0 8px 24px rgba(0,0,0,.18)}
.lw-face-capture__status--success{color:#166534;font-weight:600}
.lw-face-capture__ring{position:absolute;inset:-6px;border-radius:50%;border:2px solid rgba(255,255,255,.35);pointer-events:none;box-shadow:0 0 0 1px rgba(0,0,0,.08)}
.lw-face-capture__preview-wrap.is-ready .lw-face-capture__ring{border-color:rgba(22,163,74,.55)}
.lw-face-capture__steps{display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;width:100%;max-width:20rem;margin:0;padding:0;list-style:none}
.lw-face-capture__step{font-size:.75rem;line-height:1.2;padding:.35rem .6rem;border-radius:999px;border:1px solid var(--lw-border-soft);color:var(--lw-text-muted);background:var(--lw-bg-surface)}
.lw-face-capture__step.is-active{border-color:var(--lw-border-accent-strong);color:var(--lw-accent-text);background:var(--lw-bg-accent-soft);font-weight:600}
.lw-face-capture__step.is-done{border-color:#86efac;color:#166534;background:#f0fdf4}
.lw-face-capture__preview-wrap.is-placeholder .lw-face-capture__placeholder-text{display:block}
.lw-face-capture__placeholder-text{display:none;margin:0;padding:1.25rem;text-align:center;font-size:.875rem;color:rgba(255,255,255,.75);line-height:1.45}
.lw-face-capture__video,.lw-face-capture__preview{display:block;width:100%;height:100%;object-fit:cover;border-radius:50%}
.lw-face-capture__preview-wrap:not(.is-placeholder) .lw-face-capture__placeholder-text{display:none}
.lw-face-capture__status{width:100%;max-width:20rem;text-align:center}
.lw-face-capture__actions{display:grid;gap:.5rem;width:100%;max-width:20rem}
.lw-face-capture__start-btn{width:100%}
.lw-face-capture__canvas{display:none}
@media (max-width:640px){.lw-face-capture__frame{width:min(16rem,100%)}.lw-face-capture__actions,.lw-face-capture__status{max-width:100%}}
.lw-letter-draft-form{display:none}
.lw-letter-compose-toolbar-wrap{margin-top:1rem;padding-top:1rem;border-top:1px solid var(--lw-border-soft)}
.lw-letter-compose-toolbar{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
.lw-letter-compose-toolbar .lw-panel-btn{margin:0}
.lw-letter-compose-wa-form{margin:0;display:inline-flex}
.lw-panel-btn--wa{display:inline-flex;align-items:center;gap:.375rem}
.lw-letter-compose-wa-icon{flex-shrink:0}
.lw-letter-compose-wa-note,.lw-letter-compose-wa-status{margin:.5rem 0 0;font-size:.8125rem}
.lw-letter-compose-pdf-links{display:flex;flex-wrap:wrap;gap:.35rem .5rem;align-items:center;margin-top:.65rem;font-size:.8125rem}
.lw-letter-compose-pdf-sep{color:var(--lw-text-muted)}
.lw-letter-compose-pdf-meta{color:var(--lw-text-muted);margin-left:.15rem}
.lw-letter-compose-status{margin:.65rem 0 0;padding:.45rem .75rem;font-size:.8125rem;line-height:1.45;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem;width:100%;max-width:100%}
.lw-letter-compose-status[data-state="ok"]{color:#047857;background:#ecfdf5;border-color:#a7f3d0}
.lw-letter-compose-status[hidden]{display:none!important}
@media(max-width:639px){.lw-letter-compose-toolbar{flex-direction:column;align-items:stretch}.lw-letter-compose-toolbar .lw-panel-btn{width:100%;justify-content:center}}
.lw-letter-preview-card{border:1px solid var(--lw-border);border-radius:1rem;background:#fff;overflow:hidden;box-shadow:0 4px 24px rgba(15,23,42,.06);transition:box-shadow .25s ease,border-color .25s ease}
.lw-letter-preview-card.is-preview-loading{opacity:.92}
.lw-letter-preview-card.is-preview-focused{box-shadow:0 0 0 3px rgba(37,99,235,.18),0 8px 32px rgba(15,23,42,.1);border-color:rgba(37,99,235,.35)}
.lw-letter-preview-header{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:.75rem;padding:1rem 1.125rem .875rem;border-bottom:1px solid var(--lw-border-soft);background:linear-gradient(180deg,var(--lw-bg-surface) 0%,#fff 100%)}
.lw-letter-preview-header-text{flex:1;min-width:12rem}
.lw-letter-preview-eyebrow{margin:0 0 .2rem;font-size:.6875rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--lw-text-muted)}
.lw-letter-preview-title{margin:0;font-size:1.0625rem;font-weight:600;color:var(--lw-text-primary);line-height:1.3}
.lw-letter-preview-meta{margin:.35rem 0 0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.45}
.lw-letter-preview-toolbar-actions{display:flex;flex-wrap:wrap;gap:.35rem;align-items:center}
.lw-letter-preview-action-group{display:flex;flex-wrap:wrap;gap:.35rem;align-items:center}
.lw-letter-preview-action{display:inline-flex;align-items:center;justify-content:center;padding:.35rem .7rem;font-size:.8125rem;font-weight:500;line-height:1.25;color:var(--lw-text-secondary);background:#fff;border:1px solid var(--lw-border);border-radius:.5rem;cursor:pointer;text-decoration:none;transition:background .15s ease,border-color .15s ease,color .15s ease}
.lw-letter-preview-action:hover{background:var(--lw-bg-surface);color:var(--lw-text-primary);border-color:var(--lw-border-accent-strong)}
.lw-letter-preview-action--link{display:inline-flex}
.lw-letter-preview-status-bar{padding:0 1.125rem .75rem;background:#fff}
.lw-letter-preview-status{margin:0;padding:.45rem .75rem;font-size:.8125rem;line-height:1.45;color:var(--lw-text-muted);background:var(--lw-bg-surface);border-radius:.5rem;border:1px solid var(--lw-border-soft)}
.lw-letter-preview-status[data-state="ok"]{color:#047857;background:#ecfdf5;border-color:#a7f3d0}
.lw-letter-preview-status[data-state="warn"]{color:#b45309;background:#fffbeb;border-color:#fde68a}
.lw-letter-preview-status[data-state="error"]{color:#b91c1c;background:#fef2f2;border-color:#fecaca}
.lw-letter-preview-status[data-state="loading"]{color:var(--lw-accent);background:#eff6ff;border-color:#bfdbfe}
.lw-letter-preview-body{min-height:0}
.lw-letter-preview-stage{padding:1rem 1.125rem 1.25rem;background:var(--lw-bg-surface);min-height:min(70vh,640px);max-height:75vh;overflow:auto}
.lw-letter-preview-placeholder{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;min-height:min(56vh,480px);padding:2rem 1.25rem;border:2px dashed var(--lw-border);border-radius:.75rem;background:#fff}
.lw-letter-preview-placeholder-icon{margin-bottom:.75rem;color:var(--lw-text-muted);opacity:.65}
.lw-letter-preview-placeholder-title{margin:0 0 .35rem;font-size:1rem;font-weight:600;color:var(--lw-text-primary)}
.lw-letter-preview-placeholder-text{margin:0;max-width:22rem;font-size:.875rem;line-height:1.5;color:var(--lw-text-muted)}
.lw-letter-preview-document-shell{width:100%}
.lw-letter-preview-document-shell[hidden]{display:none!important}
.lw-letter-preview-placeholder[hidden]{display:none!important}
.lw-letter-preview-document{width:100%;max-width:100%;margin:0 auto;padding:12mm 15mm;background:#fff;border:1px solid var(--lw-border-soft);border-radius:.75rem;box-shadow:0 2px 12px rgba(15,23,42,.06);overflow:auto;font-family:"Times New Roman",Times,serif;font-size:12pt;line-height:1.5;color:#111}
.lw-letter-preview-card.is-preview-loaded .lw-letter-preview-document{border-color:var(--lw-border)}
.lw-letter-compose-applicant-toolbar{display:flex;justify-content:flex-end;margin-bottom:.5rem}
.lw-letter-compose-fields-note{margin-bottom:.75rem}
.lw-letter-compose-fields{display:grid;gap:.875rem 1rem;margin-top:.75rem}
.lw-letter-compose-fields .lw-panel-field-input:read-only{background:var(--lw-bg-muted);color:var(--lw-text-secondary);cursor:default}
@media(min-width:640px){.lw-letter-compose-fields{grid-template-columns:repeat(2,minmax(0,1fr))}}
.lw-letter-compose-fields .lw-panel-field{margin:0}
.lw-letter-compose-fields .lw-panel-field:has(textarea){grid-column:1/-1}
.lw-letter-show-card{display:flex;flex-direction:column;gap:.75rem}
.lw-letter-show-lead{margin:0}
.lw-letter-show-status{margin:0;padding:.45rem .75rem;font-size:.8125rem;line-height:1.45;color:#047857;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:.5rem}
.lw-letter-show-links{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem .5rem}
.lw-letter-show-sep{color:#94a3b8}
.lw-letter-show-primary-btn{display:block;width:100%;text-align:center}
.lw-letter-show-secondary-btn{display:block;width:100%;text-align:center}
.lw-letter-preview-empty{margin:0;color:var(--lw-text-muted);font-size:.875rem;text-align:center;padding:2rem 1rem}
.lw-letter-preview-pdf-wrap{min-height:min(70vh,640px);background:#525659;border-radius:0 0 1rem 1rem;overflow:hidden}
.lw-letter-preview-pdf{display:block;width:100%;min-height:min(70vh,640px);height:75vh;border:0;background:#525659}
.lw-panel-btn--sm{padding:.35rem .75rem;font-size:.8125rem}
@media(max-width:639px){
.lw-letter-preview-header{flex-direction:column;align-items:stretch}
.lw-letter-preview-toolbar-actions{width:100%}
.lw-letter-preview-action-group{flex-direction:column;align-items:stretch;width:100%}
.lw-letter-preview-action,.lw-letter-preview-action--link{width:100%}
}
@media(max-width:1099px){
.lw-letter-compose-editor{max-height:none}
.lw-letter-preview-stage{min-height:420px;max-height:60vh}
.lw-letter-preview-placeholder{min-height:360px}
.lw-letter-preview-pdf-wrap,.lw-letter-preview-pdf{min-height:420px;height:60vh}
}
.lw-doc-viewer-body{margin:0;min-height:100vh;display:flex;flex-direction:column;background:#525659}
.lw-doc-viewer-header{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;padding:.65rem 1rem;background:#323639;color:#f1f5f9;border-bottom:1px solid #1e2124}
.lw-doc-viewer-title{margin:0;font-size:.9375rem;font-weight:600;line-height:1.35}
.lw-doc-viewer-date{margin:.2rem 0 0;font-size:.75rem;font-weight:400;color:#94a3b8;line-height:1.35}
.lw-doc-viewer-header-actions{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
.lw-doc-viewer-header .lw-panel-btn--secondary{background:#4a4f54;border-color:#6b7280;color:#f9fafb}
.lw-doc-viewer-header .lw-panel-btn--secondary:hover{background:#5c6369;color:#fff}
.lw-doc-viewer-main{flex:1 1 auto;min-height:0;display:flex}
.lw-doc-viewer-frame{flex:1 1 auto;width:100%;min-height:calc(100vh - 3.25rem);border:0;background:#525659}
@media print{
body.lw-letter-print-preview *{visibility:hidden}
body.lw-letter-print-preview #letter-preview-html-wrap,
body.lw-letter-print-preview #letter-preview-html-wrap *{visibility:visible}
body.lw-letter-print-preview #letter-preview-placeholder{display:none!important}
body.lw-letter-print-preview #letter-preview-html-wrap{position:absolute;left:0;top:0;width:100%;max-height:none;overflow:visible;background:#fff;padding:0}
body.lw-letter-print-preview #letter-preview-document-shell{visibility:visible}
body.lw-letter-print-preview #letter-preview-document{max-width:none;box-shadow:none;margin:0;padding:12mm 15mm;border:0;border-radius:0}
}
.lw-panel-card--full{max-width:none}
/* ——— Halaman publik dalam (lw-page-inner) ——— */
.lw-page-inner .lw-main{padding-top:.75rem;padding-bottom:1.5rem}
@media(min-width:640px){.lw-page-inner .lw-main{padding-top:1rem;padding-bottom:1.75rem}}
.lw-page-inner .lw-profile-hero--v2 .lw-profile-hero__inner{padding:.75rem 0 1rem}
.lw-page-inner .lw-profile-hero__title{font-size:clamp(1.25rem,2.8vw,1.625rem)}
.lw-page-inner .lw-profile-hero__lead{margin-top:.5rem;font-size:.875rem}
.lw-page-inner .lw-profile-page,.lw-page-inner .lw-services-page,.lw-page-inner .lw-kegiatan-page,.lw-page-inner .lw-contact-page,.lw-page-inner .lw-auth-page-wrapper,.lw-page-inner .lw-security-page{gap:1rem}
.lw-page-inner .lw-profile-board,.lw-page-inner .lw-services-board,.lw-page-inner .lw-contact-board,.lw-page-inner .lw-auth-board,.lw-page-inner .lw-security-board{gap:1.25rem;padding-bottom:1.5rem}
.lw-page-inner .lw-public-section-stack{gap:1rem}
.lw-page-inner .lw-service-hub-card{padding:1rem}
.lw-page-inner .lw-form-card{padding:1.125rem 1rem}
@media(min-width:640px){.lw-page-inner .lw-form-card{padding:1.25rem 1.25rem}}
.lw-page-inner .lw-profile-lurah-card{padding:1.125rem 1rem}
@media(min-width:640px){.lw-page-inner .lw-profile-lurah-card{padding:1.375rem 1.25rem}}
.lw-page-inner .lw-profile-rt-section{padding:1.125rem 1rem}
@media(min-width:640px){.lw-page-inner .lw-profile-rt-section{padding:1.375rem 1.25rem}}
.lw-page-inner .lw-auth-split{padding:.875rem 1rem}
.lw-page-inner .lw-auth-split__card{padding:1.125rem 1.25rem}
.lw-page-inner .lw-auth-split__illust-inner{padding:.875rem 1rem;gap:.75rem}
.lw-page-inner .lw-auth-split__svg{max-height:7rem}
@media(min-width:768px){.lw-page-inner .lw-auth-split__svg{max-height:8.5rem}}
.lw-page-inner .lw-auth-split__form{gap:.875rem;margin-top:1rem}
.lw-page-inner .lw-auth-split__form .lw-form-input{min-height:2.5rem;padding:.5rem .75rem;font-size:.875rem}
.lw-page-inner .lw-auth-split__submit{min-height:2.5rem;padding:.625rem 1rem;font-size:.875rem}
.lw-page-inner .lw-auth-split__title{font-size:clamp(1.125rem,2.5vw,1.375rem)}
.lw-page-inner .lw-auth-split__note{margin-top:1rem}
.lw-page-inner .lw-form-label{font-size:.8125rem}
.lw-page-inner .lw-form-card .lw-form-label{font-size:.8125rem}
.lw-page-inner .lw-form-card .lw-form-input,.lw-page-inner .lw-form-card .lw-form-select,.lw-page-inner .lw-form-card .lw-form-textarea{min-height:2.5rem;font-size:.875rem;padding:.5rem .75rem}
.lw-page-inner .lw-section-title{font-size:clamp(1.0625rem,2.5vw,1.25rem)}
.lw-page-inner .lw-profile-section-lead{font-size:.875rem}
.lw-page-inner .lw-profile-section-head{margin-bottom:1rem}
.lw-page-inner .lw-profile-page .lw-profile-section-head.lw-home-section-head{margin-bottom:1rem}
.lw-page-inner .lw-service-hub-card-title{font-size:1rem}
.lw-page-inner .lw-service-hub-card-desc{font-size:.8125rem}
.lw-page-inner .lw-service-hub-card-icon{width:2.5rem;height:2.5rem}
.lw-page-inner .lw-profile-lurah-card__name{font-size:1.125rem}
.lw-page-inner .lw-profile-rt-card__name{font-size:.9375rem}
.lw-page-inner .lw-profile-wilayah__list dd{font-size:.8125rem}
.lw-page-inner .lw-activities-body{padding:1rem 0 1.5rem}
.lw-page-inner .lw-activities-toolbar{margin-bottom:1rem}
.lw-page-inner .lw-activities-filter__chip{font-size:.8125rem}
.lw-page-inner .lw-activities-event-card__title{font-size:.9375rem}
.lw-page-inner .lw-kegiatan-page .lw-kegiatan-card-name{font-size:.9375rem}
.lw-page-inner .lw-track-page{gap:1rem}
.lw-page-inner .lw-track-page .lw-track-board{gap:1.25rem;padding-bottom:1.5rem}
.lw-page-inner .lw-track-hero-grid{gap:1rem}
.lw-page-inner .lw-track-intro{gap:.875rem;padding:0}
.lw-page-inner .lw-track-intro__title{font-size:clamp(1.25rem,2.8vw,1.625rem)}
.lw-page-inner .lw-track-intro__lead{font-size:.875rem}
.lw-page-inner .lw-track-page .lw-track-form-card{padding:1.125rem 1.25rem}
.lw-page-inner .lw-track-split__head{margin-bottom:1rem;padding-bottom:.875rem}
.lw-page-inner .lw-track-split__title{font-size:clamp(1.125rem,2.5vw,1.375rem)}
.lw-page-inner .lw-track-split__lead{font-size:.875rem}
.lw-page-inner .lw-track-page .lw-track-split__form .lw-form-label,.lw-page-inner .lw-track-page .lw-track-split__alt-form .lw-form-label{font-size:.8125rem}
.lw-page-inner .lw-track-page .lw-track-split__form .lw-form-input,.lw-page-inner .lw-track-page .lw-track-split__alt-form .lw-form-input{min-height:2.5rem;padding:.5rem .75rem;font-size:.875rem;border-radius:.625rem}
.lw-page-inner .lw-track-split__submit{min-height:2.5rem;padding:.625rem 1rem;font-size:.875rem;border-radius:.75rem}
.lw-page-inner .lw-track-split__submit--sm{min-height:2.5rem;font-size:.875rem}
.lw-page-inner .lw-track-split__form{gap:.875rem}
.lw-page-inner .lw-track-benefit__text strong{font-size:.875rem}
.lw-page-inner .lw-track-benefit__text span{font-size:.8125rem}
.lw-page-inner .lw-track-info-card{padding:1.125rem 1.25rem;gap:1rem}
.lw-page-inner .lw-track-info-card__title{font-size:clamp(1.0625rem,2.5vw,1.25rem)}
.lw-page-inner .lw-track-divider{margin-top:1rem;padding-top:1rem}
.lw-page-inner .lw-track-form-card .lw-track-alt summary{font-size:.875rem;padding:.625rem .875rem}
.lw-page-inner .lw-profile-hero__lead{text-align:left}
.lw-page-inner .lw-profile-lurah-card__body{gap:1rem;margin-top:1rem}
.lw-page-inner .lw-profile-lurah-card__body--rt-style .lw-profile-rt-card__photo{width:4rem;height:4rem}
.lw-page-inner .lw-profile-lurah-card__desc{font-size:.875rem}
.lw-page-inner .lw-profile-lurah-card__role{font-size:.8125rem}
.lw-page-inner .lw-profile-detail-vision{padding:.875rem 1rem;font-size:.8125rem}
.lw-page-inner .lw-profile-detail-vision dd{font-size:.875rem}
.lw-page-inner .lw-profile-rt-grid{gap:.875rem}
.lw-page-inner .lw-profile-rt-card{padding:1rem .875rem}
.lw-page-inner .lw-profile-rt-card__photo{width:4rem;height:4rem}
@media(min-width:1024px){.lw-page-inner .lw-profile-rt-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:1280px){.lw-page-inner .lw-profile-rt-grid{grid-template-columns:repeat(3,1fr)}}
.lw-page-inner .lw-profile-wilayah{padding:1rem}
.lw-page-inner .lw-profile-wilayah__title{font-size:.9375rem}
.lw-page-inner .lw-profile-wilayah__desc{font-size:.8125rem;margin-top:.75rem}

/* ——— Beranda (lw-page-home) ——— */
.lw-page-home.lw-shell{position:relative}
.lw-page-home .lw-nav{position:relative;z-index:10}
.lw-page-home .lw-main{padding-top:0;padding-bottom:1.5rem;position:relative;z-index:1}
.lw-page-home .lw-footer{position:relative;z-index:1}
.lw-page-home .lw-home-hero-v2{margin-inline:calc(-1 * var(--lw-content-gutter));margin-top:calc(-1 * var(--lw-nav-band))}
.lw-page-home .lw-home-hero-v3-shell{padding:0;border:none;border-radius:0;box-shadow:none;background:transparent}
.lw-page-home .lw-home-hero-v3-shell--bg{background-image:var(--lw-home-hero-bg-image);background-size:cover;background-position:center center;background-repeat:no-repeat;min-height:clamp(20rem,45vmin,30rem);display:flex;align-items:flex-end;border-radius:0 0 1.25rem 1.25rem;overflow:hidden;padding-top:calc(var(--lw-nav-band) + clamp(1rem,3vw,1.5rem));padding-bottom:clamp(1.5rem,3.5vw,2.25rem)}
.lw-page-home .lw-home-hero-v3-overlay{background:linear-gradient(135deg,rgba(6,78,59,.82) 0%,rgba(15,118,110,.55) 45%,rgba(6,78,59,.35) 100%)}
@media(max-width:639px){.lw-page-home .lw-home-hero-v3-overlay{background:linear-gradient(180deg,rgba(6,78,59,.88) 0%,rgba(15,118,110,.6) 50%,rgba(6,78,59,.4) 100%)}}
.lw-page-home .lw-home-hero-v2-content{padding:0 var(--lw-content-gutter)}
.lw-page-home .lw-home-hero-v2-content--modern{width:100%;max-width:48rem}
.lw-page-home .lw-home-page,.lw-page-home .lw-home-sections{gap:1rem}
.lw-page-home .lw-home-section{padding:1.25rem 0}
.lw-page-home .lw-home-section-head{margin-bottom:.75rem}
.lw-page-home .lw-section-title{font-size:clamp(1.0625rem,2.5vw,1.25rem)}
.lw-page-home .lw-home-hero-v2-title{display:flex;flex-direction:column;gap:.5rem;color:#fff;font-size:clamp(1.5rem,4.2vw,2.25rem);font-weight:800;line-height:1.25;text-shadow:0 1px 4px rgba(0,0,0,.25)}
.lw-page-home .lw-home-hero-v2-headline{display:block;text-transform:none;letter-spacing:-.02em}
.lw-page-home .lw-home-hero-v2-eyebrow{display:flex;align-items:center;gap:.4rem;margin:0 0 .75rem;font-size:.8125rem;font-weight:600;color:#d1fae5;line-height:1.4}
.lw-page-home .lw-home-hero-v2-eyebrow .lw-hero-eyebrow-dot{background:#6ee7b7}
.lw-page-home .lw-home-hero-v2-link{margin:.875rem 0 0;font-size:.875rem;color:#d1fae5;line-height:1.5;text-shadow:0 1px 2px rgba(0,0,0,.15)}
.lw-page-home .lw-home-hero-v2-link .lw-inline-link{color:#fff;font-weight:600;text-decoration:underline;text-underline-offset:2px}
.lw-page-home .lw-home-hero-v2-link .lw-inline-link:hover{color:#ecfdf5}
.lw-page-home .lw-home-hero-v2-tagline{display:block;max-width:min(42rem,100%);color:#a7f3d0;font-size:clamp(1rem,2.8vw,1.375rem);font-weight:600;line-height:1.35;text-shadow:0 1px 3px rgba(0,0,0,.2)}
.lw-page-home .lw-home-hero-v2-tagline--short{font-size:clamp(.875rem,2.2vw,1.0625rem);font-weight:500;color:#d1fae5;max-width:min(36rem,100%)}
.lw-page-home .lw-home-hero-v2-actions--modern{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1rem}
.lw-page-home .lw-home-hero-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.25rem;border-radius:.75rem;font-size:.875rem;font-weight:600;text-decoration:none;transition:background .15s,border-color .15s,box-shadow .15s}
.lw-page-home .lw-home-hero-btn svg{flex-shrink:0}
.lw-page-home .lw-home-hero-btn--primary{background:#fff;color:var(--lw-accent-text);box-shadow:0 2px 8px rgba(0,0,0,.12)}
.lw-page-home .lw-home-hero-btn--primary:hover{background:#ecfdf5;box-shadow:0 4px 12px rgba(0,0,0,.15)}
.lw-page-home .lw-home-hero-btn--secondary{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.35)}
.lw-page-home .lw-home-hero-btn--secondary:hover{background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.5)}
@media(max-width:639px){.lw-page-home .lw-home-hero-v2-actions--modern{flex-direction:column}.lw-page-home .lw-home-hero-btn{width:100%;justify-content:center}.lw-page-home .lw-home-section{padding:1rem 0}}
.lw-page-home .lw-home-hero-v2-lead{margin-top:.5rem;font-size:.875rem}
.lw-page-home .lw-home-hero-v2-actions{margin-top:1rem}
.lw-page-home .lw-home-hero-v2-actions .lw-btn-primary,.lw-page-home .lw-home-hero-v2-actions .lw-btn-secondary{padding:.5625rem 1.125rem}
.lw-page-home .lw-hero-note{font-size:.75rem}
.lw-page-home .lw-home-intro-lead{margin-bottom:1rem;font-size:.875rem}
.lw-page-home .lw-home-feature-card{padding:1rem 1.125rem}
.lw-page-home .lw-home-feature-icon{width:2.5rem;height:2.5rem;margin-bottom:.625rem}
.lw-page-home .lw-home-service-duo-card{padding:1.25rem 1.375rem}
.lw-page-home .lw-home-service-duo-icon{width:2.5rem;height:2.5rem}
.lw-page-home .lw-home-service-duo-title{font-size:1rem}
.lw-page-home .lw-home-services-duo .lw-home-section-head{margin-bottom:1rem}
.lw-home-page{display:flex;flex-direction:column;gap:var(--lw-section-gap);min-width:0;width:100%}
.lw-home-sections{gap:var(--lw-section-gap)}
.lw-home-section{padding:2.5rem 0;border-bottom:1px solid var(--lw-border-soft)}
.lw-home-section:first-child{padding-top:0}
.lw-home-section:last-of-type{border-bottom:none}
.lw-home-section-head{margin-bottom:1.25rem}
.lw-home-section-head--row{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1rem}
.lw-home-section-link{font-size:.875rem;font-weight:600;color:var(--lw-accent);text-decoration:none;white-space:nowrap}
.lw-home-section-link:hover{text-decoration:underline;color:var(--lw-accent-hover)}
.lw-home-intro-lead{margin:0 0 1.5rem;max-width:48rem;font-size:1rem;line-height:1.7;color:var(--lw-text-secondary)}
.lw-home-intro-advantages{margin-top:1.75rem;padding-top:1.75rem;border-top:1px solid var(--lw-border-soft)}
.lw-home-intro-advantages>.lw-section-title{margin:0 0 .875rem;font-size:1.125rem;line-height:1.3}
.lw-home-advantage-grid{display:grid;gap:var(--lw-card-gap);grid-template-columns:minmax(0,1fr);width:100%}
@media(min-width:640px){.lw-home-advantage-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1024px){.lw-home-advantage-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
.lw-home-advantage-card{padding:1.125rem 1.25rem;border-radius:.875rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);box-shadow:var(--lw-shadow-sm)}
.lw-home-advantage-icon{display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;background:var(--lw-bg-accent-soft);color:var(--lw-accent);margin-bottom:.625rem}
.lw-home-advantage-title{margin:0 0 .35rem;font-size:.9375rem;font-weight:700;color:var(--lw-text-strong)}
.lw-home-advantage-desc{margin:0;font-size:.8125rem;line-height:1.55;color:var(--lw-text-muted)}
.lw-home-intro-alur{margin-top:1.75rem;padding-top:1.75rem;border-top:1px solid var(--lw-border-soft)}
.lw-home-intro-alur-head{margin-bottom:1.25rem}
.lw-home-intro-alur .lw-section-title{font-size:1.125rem}
.lw-home-intro-alur .lw-home-process-lead{margin-top:.375rem;max-width:48rem}
.lw-home-hero-v2{margin-bottom:0;padding-bottom:0}
.lw-home-hero-v3-shell{border-radius:1.25rem;border:1px solid var(--lw-border-accent);background:var(--lw-hero-bg);box-shadow:var(--lw-shadow-sm);padding:1.5rem 1.25rem}
@media(min-width:640px){.lw-home-hero-v3-shell{padding:2rem 1.75rem}}
@media(min-width:1024px){.lw-home-hero-v3-shell{padding:2.25rem 2rem}}
.lw-home-hero-v3-shell--bg{position:relative;overflow:hidden;background-image:var(--lw-home-hero-bg-image);background-size:cover;background-position:center 35%;background-repeat:no-repeat;min-height:clamp(18rem,40vh,28rem)}
.lw-home-hero-v3-overlay{position:absolute;inset:0;border-radius:inherit;pointer-events:none;background:linear-gradient(105deg,rgba(255,255,255,.95) 0%,rgba(236,253,245,.9) 42%,rgba(240,253,250,.75) 62%,rgba(15,118,110,.2) 100%)}
@media(max-width:639px){.lw-home-hero-v3-overlay{background:linear-gradient(180deg,rgba(255,255,255,.96) 0%,rgba(236,253,245,.92) 55%,rgba(240,253,250,.78) 78%,rgba(15,118,110,.22) 100%)}}
.lw-home-hero-v3-shell--bg .lw-home-hero-v2-content{position:relative;z-index:1;max-width:min(42rem,100%);min-width:0}
.lw-home-hero-v2-grid{display:grid;gap:2rem;align-items:center}
@media(min-width:1024px){.lw-home-hero-v2-grid{grid-template-columns:1fr 1fr;gap:2.75rem}}
.lw-home-hero-v2-title{margin:0;font-size:clamp(1.5rem,4vw + .5rem,2.375rem);font-weight:800;line-height:1.2;color:var(--lw-text-strong);letter-spacing:-.03em;overflow-wrap:anywhere}
.lw-home-hero-v3-title-accent{color:var(--lw-accent);display:inline}
.lw-home-hero-v2-lead{margin:.875rem 0 0;max-width:min(36rem,100%);font-size:1rem;line-height:1.65;color:var(--lw-text-secondary)}
.lw-home-hero-v3-shell--bg .lw-hero-note{max-width:min(36rem,100%)}
.lw-home-hero-v2-points{margin:1.25rem 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:.625rem}
.lw-home-hero-v2-points li{display:flex;align-items:flex-start;gap:.625rem;font-size:.9375rem;color:var(--lw-text-body);line-height:1.5}
.lw-home-hero-v2-point-icon{flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;width:1.25rem;height:1.25rem;border-radius:9999px;background:var(--lw-bg-accent-soft);color:var(--lw-accent);font-size:.6875rem;font-weight:700;margin-top:.15rem}
.lw-home-hero-v3-point-icon{flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;width:1.375rem;height:1.375rem;border-radius:9999px;background:var(--lw-bg-accent-soft);color:var(--lw-accent);margin-top:.1rem}
.lw-home-hero-v2-actions{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.5rem}
.lw-home-hero-v2-actions .lw-btn-primary,.lw-home-hero-v2-actions .lw-btn-secondary{padding:.6875rem 1.375rem;font-size:.875rem;border-radius:.75rem;min-width:min(100%,12rem)}
@media(max-width:639px){.lw-home-hero-v2-actions{flex-direction:column}.lw-home-hero-v2-actions .lw-btn-primary,.lw-home-hero-v2-actions .lw-btn-secondary{width:100%;justify-content:center}}
.lw-home-hero-v3-shell{min-width:0}
.lw-home-hero-v2-stats{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--lw-border-soft)}
.lw-home-hero-v2-stat{flex:1;min-width:7rem;padding:.75rem 1rem;border-radius:.75rem;background:var(--lw-bg-muted);border:1px solid var(--lw-border-accent);text-align:center}
.lw-home-hero-v2-stat-value{display:block;font-size:1.375rem;font-weight:800;color:var(--lw-accent-dark);line-height:1}
.lw-home-hero-v2-stat-label{display:block;margin-top:.25rem;font-size:.6875rem;font-weight:500;color:var(--lw-text-muted);text-transform:uppercase;letter-spacing:.04em}
.lw-home-hero-visual{margin:0;position:relative}
.lw-home-hero-visual-frame{position:relative;border-radius:1.25rem;overflow:hidden;box-shadow:var(--lw-shadow-md);background:var(--lw-bg-card)}
.lw-home-hero-visual-frame::before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,rgba(15,118,110,.08) 0%,transparent 55%);pointer-events:none;z-index:1;border-radius:inherit}
.lw-home-hero-visual-frame::after{content:"";position:absolute;bottom:-1.5rem;right:-1.5rem;width:40%;height:40%;border-radius:9999px;background:var(--lw-accent-warm);opacity:.35;filter:blur(40px);z-index:0}
.lw-home-hero-visual-img{display:block;width:100%;height:auto;aspect-ratio:4/3;object-fit:cover;object-position:center 35%;position:relative;z-index:0;transition:transform .4s ease}
.lw-home-hero-visual:hover .lw-home-hero-visual-img{transform:scale(1.02)}
.lw-home-feature-grid{display:grid;gap:var(--lw-card-gap);grid-template-columns:minmax(0,1fr);width:100%}
.lw-home-feature-grid--six{grid-template-columns:minmax(0,1fr)}
@media(min-width:640px){.lw-home-feature-grid--six{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1024px){.lw-home-feature-grid--six{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(min-width:640px){.lw-home-feature-grid:not(.lw-home-feature-grid--six){grid-template-columns:repeat(3,minmax(0,1fr))}}
.lw-home-wa-strip{display:flex;align-items:flex-start;gap:.75rem;margin-top:1.25rem;padding:1rem 1.125rem;border-radius:1rem;border:1px solid var(--lw-border-accent);background:var(--lw-bg-accent-soft)}
.lw-home-wa-strip__icon{flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;border-radius:.75rem;background:var(--lw-bg-card);color:var(--lw-accent);border:1px solid var(--lw-border-accent-strong)}
.lw-home-wa-strip__text{margin:0;font-size:.875rem;line-height:1.55;color:var(--lw-text-secondary)}
.lw-track-flow-grid{margin-top:.25rem}
@media(min-width:1024px){.lw-track-info-card .lw-track-flow-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
.lw-home-feature-card{width:100%;min-width:0;box-sizing:border-box;padding:1.25rem 1.375rem;border-radius:1rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);box-shadow:var(--lw-shadow-sm);transition:transform .2s,box-shadow .2s}
.lw-home-feature-card--link{display:block;text-decoration:none;color:inherit;cursor:pointer}
.lw-home-feature-card:hover,.lw-home-feature-card--link:hover{transform:translateY(-2px);box-shadow:var(--lw-shadow-md)}
.lw-home-feature-icon{display:inline-flex;align-items:center;justify-content:center;width:2.75rem;height:2.75rem;border-radius:.75rem;background:var(--lw-bg-accent-soft);color:var(--lw-accent);margin-bottom:.75rem}
.lw-home-feature-title{margin:0 0 .35rem;font-size:1rem;font-weight:700;color:var(--lw-text-strong)}
.lw-home-feature-desc{margin:0;font-size:.875rem;line-height:1.55;color:var(--lw-text-muted)}
.lw-home-service-duo-grid{display:grid;gap:var(--lw-card-gap);grid-template-columns:minmax(0,1fr);width:100%}
@media(min-width:768px){.lw-home-service-duo-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--lw-section-gap)}}
.lw-home-service-duo-card{display:flex;flex-direction:column;gap:.75rem;padding:1.5rem 1.625rem;border-radius:1.125rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);box-shadow:var(--lw-shadow-sm);transition:transform .2s,box-shadow .2s}
.lw-home-service-duo-card:hover{transform:translateY(-2px);box-shadow:var(--lw-shadow-md)}
.lw-home-service-duo-card--pendataan{background:var(--lw-pendataan-card-bg);border-color:var(--lw-notice-border)}
.lw-home-service-duo-icon{display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;margin:0;border-radius:.875rem;background:var(--lw-bg-accent-soft);color:var(--lw-accent)}
.lw-home-service-duo-title{margin:0;font-size:1.125rem;font-weight:700;color:var(--lw-accent-text)}
.lw-home-service-duo-desc{margin:0;font-size:.875rem;line-height:1.6;color:var(--lw-text-secondary);flex-grow:1}
.lw-home-service-duo-list{margin:0;padding-inline-start:1rem;font-size:.8125rem;line-height:1.7;color:var(--lw-text-muted)}
.lw-home-service-duo-list li{margin:0}
.lw-home-service-duo-list li+li{margin-top:.25rem}
.lw-home-service-duo-btn{margin-top:auto;width:fit-content}
.lw-home-kelurahan-notice{margin-top:1.5rem}
.lw-page-home #alur,.lw-page-home #panduan,.lw-page-home #layanan-utama{scroll-margin-top:5.5rem}
.lw-page-home .lw-home-faq .lw-home-section-head{align-items:center;text-align:center;gap:.375rem;margin-bottom:1rem}
@media(min-width:640px){.lw-page-home .lw-home-faq .lw-home-section-head{margin-bottom:1.25rem}}
.lw-page-home .lw-home-faq .lw-section-desc{margin-left:auto;margin-right:auto}
.lw-home-faq-list{display:flex;flex-direction:column;gap:.625rem;max-width:48rem;margin-inline:auto;width:100%}
.lw-page-home .lw-home-faq-list{margin-top:.25rem}
@media(min-width:640px){.lw-page-home .lw-home-faq-list{margin-top:0}}
.lw-home-faq-item{border-radius:.875rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);overflow:hidden;transition:border-color .2s}
.lw-home-faq-item[open]{border-color:var(--lw-border-accent-strong);box-shadow:var(--lw-shadow-sm)}
.lw-home-faq-question{cursor:pointer;padding:1rem 1.125rem;font-size:.9375rem;font-weight:600;color:var(--lw-text-strong);list-style:none;display:flex;align-items:flex-start;gap:.75rem;line-height:1.45}
.lw-home-faq-question::-webkit-details-marker{display:none}
.lw-home-faq-q-text{flex:1;min-width:0;word-break:break-word;overflow-wrap:anywhere}
.lw-home-faq-chevron{flex-shrink:0;width:.625rem;height:.625rem;margin-top:.35rem;border-right:2px solid var(--lw-accent);border-bottom:2px solid var(--lw-accent);transform:rotate(45deg);transition:transform .2s}
.lw-home-faq-item[open] .lw-home-faq-chevron{transform:rotate(-135deg);margin-top:.5rem}
.lw-home-faq-answer{padding:0 1.125rem 1rem 1.125rem}
.lw-home-faq-answer p{margin:0;font-size:.875rem;line-height:1.6;color:var(--lw-text-secondary)}
@media(max-width:480px){
.lw-home-faq-question{padding:.875rem 1rem;font-size:.875rem}
.lw-home-faq-answer{padding:0 1rem .875rem}
}
.lw-visually-hidden{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.lw-page-narrow{max-width:48rem;margin-left:auto;margin-right:auto;width:100%}
.lw-page-public .lw-hero-lead,.lw-page-public .lw-section-desc{max-width:min(42rem,100%)}
.lw-auth-page:not(.lw-auth-split){max-width:28rem;margin-left:auto;margin-right:auto;width:100%}
.lw-auth-card{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:1rem;padding:1.25rem 1.5rem;box-shadow:var(--lw-shadow-sm)}
.lw-auth-label{display:block;font-size:.8125rem;font-weight:600;color:var(--lw-text-secondary);margin-bottom:.35rem;text-align:left}
.lw-track-page.lw-band--alt{max-width:28rem;margin-left:auto;margin-right:auto;width:100%}
.lw-track-back{display:inline-block;margin-bottom:1rem;font-size:.875rem;font-weight:600;color:var(--lw-accent);text-decoration:none}
.lw-track-back:hover{text-decoration:underline;color:var(--lw-accent-hover)}
.lw-track-card{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:1rem;padding:1.25rem 1.5rem;box-shadow:var(--lw-shadow-sm)}
.lw-success-card{max-width:32rem;margin:0 auto;text-align:center;padding:1.5rem 1.75rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:1rem;box-shadow:var(--lw-shadow-sm)}
.lw-application-number-card{margin:0}
.lw-application-number-block{display:flex;flex-wrap:wrap;align-items:center;gap:.75rem 1rem}
.lw-application-number-display{margin:0;flex:1 1 12rem;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:1.375rem;font-weight:700;line-height:1.35;color:var(--lw-accent-dark);word-break:break-all}
.lw-copy-text-btn{flex-shrink:0;margin:0}
.lw-copy-text-feedback{margin:.5rem 0 0;font-size:.8125rem;line-height:1.45;color:var(--lw-text-muted)}
.lw-copy-text-feedback[data-state="ok"]{color:#047857}
.lw-copy-text-feedback[data-state="error"]{color:#b91c1c}
.lw-mt-3{margin-top:.75rem}
.lw-mt-6{margin-top:1.5rem}
.lw-mb-0{margin-bottom:0}
.lw-mb-4{margin-bottom:1rem}
.lw-mb-3{margin-bottom:.75rem}
.lw-mt-2{margin-top:.5rem}
.lw-mt-4{margin-top:1rem}
.lw-panel-actions{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}
.lw-panel-actions .lw-panel-btn,.lw-panel-actions .lw-btn-secondary{margin:0}
.lw-panel-theme .lw-panel-content{padding:1rem;max-width:100%}
@media(min-width:640px){.lw-panel-theme .lw-panel-content{padding:1.25rem 1.5rem 2rem}}

/* ——— Komponen publik (hub, staff, timeline, aksesibilitas) ——— */
.lw-form-card .lw-form-input,.lw-form-card .lw-form-select,.lw-form-card .lw-form-textarea{min-height:3rem;font-size:1.0625rem}
.lw-form-card .lw-form-label{font-size:.9375rem}
.lw-track-card .lw-form-actions .lw-btn-primary,.lw-auth-card .lw-btn-primary{width:100%;justify-content:center}
.lw-service-hub-grid{display:grid;gap:var(--lw-card-gap);grid-template-columns:minmax(0,1fr);width:100%}
@media(min-width:640px){.lw-service-hub-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
.lw-service-hub-card{display:flex;gap:1rem;width:100%;min-width:0;box-sizing:border-box;padding:1.25rem;border-radius:1rem;border:1px solid var(--lw-border);background:var(--lw-bg-card);text-decoration:none;color:inherit;box-shadow:var(--lw-shadow-sm);transition:border-color .2s,box-shadow .2s,transform .15s}
.lw-service-hub-card:hover{border-color:var(--lw-border-accent-strong);box-shadow:0 8px 24px rgba(15,118,110,.1);transform:translateY(-2px)}
.lw-service-hub-card-icon{flex-shrink:0;width:3rem;height:3rem;display:flex;align-items:center;justify-content:center;border-radius:.75rem;background:var(--lw-bg-accent-soft);color:var(--lw-accent)}
.lw-service-hub-card-body{display:flex;flex-direction:column;gap:.35rem;min-width:0}
.lw-service-hub-card-title{font-size:1.0625rem;font-weight:700;color:var(--lw-text-strong);line-height:1.3}
.lw-service-hub-card-desc{font-size:.875rem;color:var(--lw-text-muted);line-height:1.5}
.lw-service-hub-card-cta{margin-top:.35rem;font-size:.8125rem;font-weight:600;color:var(--lw-accent)}
.lw-profile-staff-section{margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--lw-border-soft)}
.lw-profile-staff-heading{margin:0 0 .35rem;font-size:1.0625rem;font-weight:700;color:var(--lw-text-strong)}
.lw-profile-location-maps{margin-top:1rem}
.lw-profile-page .lw-profile-location-maps.lw-maps-wrap{margin-top:1rem}
.lw-profile-page .lw-profile-location-maps.lw-profile-map-placeholder{margin-top:1rem}
.lw-staff-grid{display:grid;gap:var(--lw-card-gap);grid-template-columns:minmax(0,1fr);width:100%;margin-top:1rem}
@media(min-width:480px){.lw-staff-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:900px){.lw-staff-grid{grid-template-columns:repeat(3,1fr)}}
.lw-staff-card{display:flex;flex-direction:column;height:100%;border:1px solid var(--lw-border);border-radius:1rem;background:var(--lw-bg-card);box-shadow:0 4px 20px rgba(15,23,42,.06);transition:box-shadow .2s,transform .15s}
.lw-staff-card:hover{box-shadow:0 8px 28px rgba(15,118,110,.1);transform:translateY(-2px)}
.lw-staff-card-inner{display:flex;flex-direction:column;align-items:center;text-align:center;padding:1.25rem 1rem;height:100%;width:100%}
.lw-staff-card-photo-wrap{position:relative;flex-shrink:0;margin-bottom:.75rem}
.lw-staff-card-photo{width:6rem;height:6rem;border-radius:9999px;object-fit:cover;border:3px solid var(--lw-border-accent-strong);box-shadow:0 4px 12px rgba(15,118,110,.12)}
.lw-staff-card-badge{position:absolute;right:0;bottom:0;display:flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:9999px;background:var(--lw-accent);color:#fff;border:2px solid var(--lw-bg-card);box-shadow:0 2px 6px rgba(15,23,42,.15)}
.lw-staff-card-badge svg{width:.875rem;height:.875rem}
.lw-staff-card-role{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--lw-accent)}
.lw-staff-card-name{margin:.35rem 0 0;font-size:1.0625rem;font-weight:700;line-height:1.35}
.lw-staff-card-name-link{color:var(--lw-text-strong);text-decoration:none}
.lw-staff-card-name-link:hover{color:var(--lw-accent);text-decoration:underline}
.lw-staff-card-footer{margin-top:auto;padding-top:1rem;width:100%;max-width:16rem}
.lw-staff-card-wa{width:100%;min-height:3rem;padding:.6875rem 1rem;font-size:.9375rem}
.lw-staff-detail-card .lw-staff-card-inner{max-width:20rem;margin:0 auto}
.lw-staff-detail-card .lw-staff-card-photo{width:7.5rem;height:7.5rem}
.lw-staff-detail-card .lw-staff-card-name{font-size:1.25rem}
.lw-staff-detail-card .lw-staff-card-bio{margin:.75rem 0 0;text-align:center;font-size:.9375rem;line-height:1.55;color:var(--lw-text-secondary)}
.lw-staff-wa-note{margin:.5rem 0 0;font-size:.75rem;color:var(--lw-text-muted);line-height:1.45}
.lw-track-result-list{list-style:none;margin:1rem 0 0;padding:0;display:flex;flex-direction:column;gap:.75rem}
.lw-track-result-card{padding:1rem 1.125rem;border:1px solid var(--lw-border-soft);border-radius:.75rem;background:var(--lw-bg-surface-muted)}
.lw-track-result-card__number{margin:0;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:.9375rem;font-weight:700;color:var(--lw-accent-dark);word-break:break-all;line-height:1.4}
.lw-track-result-card__meta{margin:.35rem 0 0;font-size:.8125rem;color:var(--lw-text-body);line-height:1.45}
.lw-track-result-card__date{margin:.25rem 0 0;font-size:.75rem;color:var(--lw-text-muted)}
.lw-service-show-requirements{margin-top:1.5rem}
.lw-service-show-warn{margin-top:1.25rem}
.lw-service-show-actions{margin-top:1.5rem}
.lw-service-show-note{margin-top:1rem}
.lw-is-hidden{display:none!important}
.lw-staff-wa-note{margin:.5rem 0 0;font-size:.75rem;line-height:1.45;color:var(--lw-text-muted)}
.lw-wa-button{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.5rem 1rem;font-size:.875rem;font-weight:600;color:#fff;background:#25a244;border-radius:.5rem;text-decoration:none;border:none;cursor:pointer;transition:background .2s}
.lw-wa-button:hover{background:#1d8a38;color:#fff}
.lw-wa-button-icon{display:flex}
.lw-timeline{margin:1.25rem 0 0;padding:0;list-style:none}
.lw-timeline-item{position:relative;display:flex;gap:1rem;padding-bottom:1.25rem}
.lw-timeline-item:last-child{padding-bottom:0}
.lw-timeline-item:not(:last-child)::before{content:"";position:absolute;left:.6875rem;top:1.5rem;bottom:0;width:2px;background:var(--lw-border)}
.lw-timeline-item--done:not(:last-child)::before{background:var(--lw-accent)}
.lw-timeline-marker{flex-shrink:0;width:1.375rem;height:1.375rem;display:flex;align-items:center;justify-content:center;z-index:1}
.lw-timeline-check{width:1.375rem;height:1.375rem;border-radius:9999px;background:var(--lw-accent);color:#fff;font-size:.75rem;font-weight:700;display:flex;align-items:center;justify-content:center}
.lw-timeline-dot{width:.75rem;height:.75rem;border-radius:9999px;background:var(--lw-accent);box-shadow:0 0 0 3px var(--lw-bg-accent-soft)}
.lw-timeline-dot--muted{background:var(--lw-border)}
.lw-timeline-item--current .lw-timeline-dot{box-shadow:0 0 0 4px rgba(15,118,110,.25)}
.lw-timeline-title{margin:0;font-size:.9375rem;font-weight:600;color:var(--lw-text-strong)}
.lw-timeline-desc{margin:.25rem 0 0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.5}
.lw-timeline-date{margin:.35rem 0 0;font-size:.75rem;color:var(--lw-text-faint)}
.lw-timeline-item--pending .lw-timeline-title{color:var(--lw-text-faint)}
.lw-page-subnav{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.5rem;padding:.5rem;border-radius:.75rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);position:sticky;top:4.5rem;z-index:20}
.lw-page-subnav-link{padding:.5rem 1rem;font-size:.875rem;font-weight:600;color:var(--lw-text-secondary);text-decoration:none;border-radius:.5rem}
.lw-page-subnav-link:hover,.lw-page-subnav-link.is-active{background:var(--lw-bg-accent-soft);color:var(--lw-accent)}
.lw-track-tabs{display:flex;flex-wrap:wrap;gap:.35rem;margin-bottom:1.25rem}
.lw-track-tab{flex:1;min-width:6rem;padding:.625rem .75rem;font-size:.8125rem;font-weight:600;text-align:center;border:1px solid var(--lw-border);border-radius:.5rem;background:var(--lw-bg-muted);color:var(--lw-text-secondary);cursor:pointer}
.lw-track-tab.is-active{background:var(--lw-accent);border-color:var(--lw-accent);color:#fff}
.lw-track-panel.hidden{display:none}
.lw-track-alt{margin-top:1.25rem;border-top:1px solid var(--lw-border-soft);padding-top:1rem}
.lw-track-alt+.lw-track-alt{margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--lw-border-soft)}
.lw-track-alt summary{font-size:.875rem;font-weight:600;color:var(--lw-accent);cursor:pointer;list-style:none}
.lw-track-alt summary::-webkit-details-marker{display:none}
.lw-track-alt[open] summary{margin-bottom:.75rem}
.lw-track-alt .lw-form-stack{margin-top:.5rem}
.lw-calendar-wrap{margin-top:1rem}
.lw-calendar-header{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem}
.lw-calendar-title{margin:0;font-size:1.125rem;font-weight:700;color:var(--lw-text-strong)}
.lw-calendar-nav{display:flex;gap:.5rem}
.lw-calendar-nav-btn{padding:.4rem .75rem;font-size:.875rem;font-weight:600;border:1px solid var(--lw-border);border-radius:.5rem;background:var(--lw-bg-card);cursor:pointer;color:var(--lw-accent)}
.lw-calendar-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:.25rem;font-size:.75rem}
.lw-calendar-dow{text-align:center;font-weight:600;color:var(--lw-text-muted);padding:.35rem}
.lw-calendar-day{aspect-ratio:1;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:.375rem;border:1px solid transparent;cursor:pointer;background:transparent;font:inherit;color:var(--lw-text-body)}
.lw-calendar-day:hover:not(:disabled){background:var(--lw-bg-accent-soft)}
.lw-calendar-day--other{color:var(--lw-text-faint)}
.lw-calendar-day--today{font-weight:700;border-color:var(--lw-accent)}
.lw-calendar-day--has-event{font-weight:700;color:var(--lw-accent)}
.lw-calendar-day--selected{background:var(--lw-accent);color:#fff}
.lw-calendar-day:disabled{cursor:default;opacity:.4}
.lw-calendar-events{margin-top:1rem;padding:1rem;border-radius:.75rem;background:var(--lw-bg-accent-muted);border:1px solid var(--lw-border-accent)}
.lw-gallery-grid{display:grid;gap:.75rem;grid-template-columns:repeat(2,1fr)}
@media(min-width:640px){.lw-gallery-grid{grid-template-columns:repeat(3,1fr)}}
@media(min-width:900px){.lw-gallery-grid{grid-template-columns:repeat(4,1fr)}}
.lw-gallery-item{position:relative;border:none;padding:0;border-radius:.75rem;overflow:hidden;cursor:pointer;background:var(--lw-bg-muted);aspect-ratio:4/3}
.lw-gallery-item img{width:100%;height:100%;object-fit:cover;display:block}
.lw-gallery-caption{position:absolute;inset:auto 0 0 0;padding:.5rem .625rem;font-size:.6875rem;font-weight:600;color:#fff;background:linear-gradient(transparent,rgba(15,23,42,.75))}
.lw-lightbox{position:fixed;inset:0;z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgba(15,23,42,.85)}
.lw-lightbox[hidden]{display:none}
.lw-lightbox-inner{max-width:48rem;width:100%;text-align:center}
.lw-lightbox img{max-width:100%;max-height:80vh;border-radius:.75rem}
.lw-lightbox-close{position:absolute;top:1rem;right:1rem;width:2.5rem;height:2.5rem;border:none;border-radius:9999px;background:#fff;font-size:1.25rem;cursor:pointer}
.lw-contact-hub-grid{display:grid;gap:1.5rem}
@media(min-width:768px){.lw-contact-hub-grid{grid-template-columns:1fr 1fr}}
.lw-contact-rt-card{padding:1rem;border:1px solid var(--lw-border);border-radius:.75rem;background:var(--lw-bg-card)}
.lw-auth-feature-grid{display:grid;gap:.75rem;margin-top:1.25rem}
@media(min-width:480px){.lw-auth-feature-grid{grid-template-columns:repeat(3,1fr)}}
.lw-auth-feature-card{padding:1rem;border-radius:.75rem;border:1px solid var(--lw-border-soft);background:var(--lw-bg-accent-muted);font-size:.8125rem}
.lw-auth-feature-card strong{display:block;margin-bottom:.35rem;color:var(--lw-accent-dark)}
.lw-maps-wrap{margin-top:1rem;border-radius:.75rem;overflow:hidden;border:1px solid var(--lw-border)}
.lw-maps-wrap iframe{display:block;width:100%;min-height:16rem;border:0}

/* ——— Responsif global (HP & laptop) ——— */
html{-webkit-text-size-adjust:100%;text-size-adjust:100%}
html.lw-zoom-floor-active{overflow-x:auto}
html.lw-zoom-floor-active .lw-shell,html.lw-zoom-floor-active .lw-panel-body{overflow-x:clip}
.lw-shell{overflow-x:clip}
.lw-main,.lw-site-frame,.lw-home-page,.lw-home-hero-v3-shell{min-width:0}
.lw-services-grid>*,.lw-catalog-grid>*,.lw-persyaratan-grid>*,.lw-service-hub-grid>*,.lw-home-feature-grid>*,.lw-home-service-duo-grid>*{min-width:0}
.lw-shell img:not(.lw-nav-portal-icon):not(.lw-footer-logo):not(.lw-staff-card-badge svg),
.lw-shell video,.lw-shell iframe{max-width:100%}
.lw-page-narrow,.lw-success-card{width:100%;box-sizing:border-box}
.lw-track-page,.lw-auth-page{box-sizing:border-box}
@media(max-width:639px){
.lw-kel-filter-grid{grid-template-columns:1fr}
.lw-kel-filter-actions{flex-direction:column;align-items:stretch}
.lw-kel-filter-actions .lw-panel-btn{width:100%;text-align:center;justify-content:center;min-height:2.75rem}
.lw-form-grid--2{grid-template-columns:1fr}
.lw-form-card .lw-form-actions{display:flex;flex-direction:column;gap:.5rem}
.lw-form-card .lw-form-actions .lw-btn-primary,.lw-form-card .lw-form-actions .lw-btn-secondary{width:100%;justify-content:center}
.lw-service-hub-grid{grid-template-columns:1fr}
.lw-page-subnav{flex-wrap:nowrap;overflow-x:auto;-webkit-overflow-scrolling:touch;gap:.35rem;padding:.5rem .625rem}
.lw-page-subnav-link{flex-shrink:0;white-space:nowrap}
.lw-hero{padding:1rem .875rem}
.lw-hero-title{font-size:1.5rem}
.lw-catalog-grid,.lw-kegiatan-grid,.lw-pengumuman-grid{grid-template-columns:1fr}
.lw-calendar-grid{font-size:.6875rem;gap:.15rem}
.lw-calendar-day{padding:.2rem}
.lw-footer{padding:.75rem .875rem}
.lw-footer-inner{gap:.625rem}
.lw-footer-brand-head{gap:.5rem}
.lw-footer-logo{width:2rem;height:2rem}
.lw-footer-text{font-size:.8125rem}
.lw-footer-copyright{font-size:.625rem;max-width:100%}
.lw-footer-bottom{align-items:center;text-align:center}
}
@media(max-width:767px){
.lw-profile-detail-grid--compact{flex-direction:column;align-items:center;text-align:center}
.lw-profile-detail-body--fill{align-items:center}
}

@media print{
body.lw-printing .lw-panel-sidebar,body.lw-printing .lw-panel-topbar,body.lw-printing .lw-panel-backdrop,body.lw-printing .lw-kel-no-print{display:none!important}
body.lw-printing .lw-panel-layout{display:block}
body.lw-printing .lw-panel-main{width:100%;max-width:none}
body.lw-printing .lw-panel-content{padding:.5rem}
body.lw-printing .lw-panel-table-wrap--wide{max-height:none;overflow:visible}
body.lw-printing .lw-kel-rt-summary details{border:none}
body.lw-printing .lw-kel-rt-summary summary::after{display:none}
body.lw-printing .lw-kel-pop-table .lw-kel-pop-age-col{display:table-cell!important}
body.lw-printing .lw-kel-pop-table .lw-kel-pop-detail-col{display:table-cell!important}
body.lw-printing .lw-panel-table--population{min-width:100%!important;font-size:.65rem}
}

/* ——— Panel admin sistem ——— */
.lw-panel--admin .lw-panel-brand-eyebrow{color:#fde68a}
.lw-panel--rt .lw-panel-brand-eyebrow{color:#a7f3d0}
.lw-panel-body.lw-panel--rt,.lw-panel--rt .lw-panel-main{overflow-x:clip;max-width:100%}
.lw-panel--rt .lw-panel-content{overflow-x:clip;max-width:100%;min-width:0}
/* Panel RT & Monitoring: daftar data warga — toolbar, filter, tabel seragam */
.lw-panel--rt .lw-panel-toolbar,
.lw-panel--kelurahan .lw-panel-toolbar,
.lw-panel--rt .lw-rt-list-toolbar,
.lw-panel--kelurahan .lw-rt-list-toolbar,
.lw-panel--rt .lw-rt-data-toolbar,
.lw-panel--kelurahan .lw-rt-data-toolbar{margin-bottom:1rem;padding:1rem 1.125rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem;align-items:flex-start}
.lw-panel--rt .lw-rt-list-toolbar,
.lw-panel--kelurahan .lw-rt-list-toolbar{display:flex;flex-wrap:wrap;gap:1rem}
.lw-panel--rt .lw-rt-list-toolbar-cta,
.lw-panel--kelurahan .lw-rt-list-toolbar-cta{flex-shrink:0}
.lw-panel--rt .lw-rt-list-toolbar-stack,
.lw-panel--kelurahan .lw-rt-list-toolbar-stack{flex:1;min-width:min(100%,16rem);display:flex;flex-direction:column;gap:.75rem}
.lw-panel--rt .lw-rt-list-toolbar-tabs .lw-rt-filter-tabs,
.lw-panel--kelurahan .lw-rt-list-toolbar-tabs .lw-rt-filter-tabs{margin-bottom:0;padding-bottom:0;border-bottom:none}
.lw-panel--rt .lw-rt-list-toolbar-filters,
.lw-panel--kelurahan .lw-rt-list-toolbar-filters{align-items:flex-end}
.lw-panel--rt .lw-rt-data-toolbar-actions,
.lw-panel--kelurahan .lw-rt-data-toolbar-actions{align-self:flex-start}
.lw-panel--rt .lw-rt-data-toolbar-filters,
.lw-panel--kelurahan .lw-rt-data-toolbar-filters{display:flex;flex-direction:column;gap:.75rem;flex:1;min-width:min(100%,20rem)}
.lw-panel--rt .lw-rt-data-search,
.lw-panel--kelurahan .lw-rt-data-search{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
.lw-panel--rt .lw-rt-data-search input[type=search],
.lw-panel--kelurahan .lw-rt-data-search input[type=search]{flex:1;min-width:12rem;padding:.5rem .75rem;border:1px solid var(--lw-border);border-radius:.5rem;font-size:.875rem;background:var(--lw-bg-card)}
.lw-panel--rt .lw-rt-data-category-tabs,
.lw-panel--kelurahan .lw-rt-data-category-tabs{margin-top:0}
.lw-panel--rt .lw-rt-data-status-tabs,
.lw-panel--kelurahan .lw-rt-data-status-tabs{margin-bottom:1rem;border-bottom:1px solid var(--lw-border);padding-bottom:.5rem}
.lw-panel--rt .lw-rt-data-page .lw-panel-page-head,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-page-head{margin-bottom:1rem}
.lw-panel--rt .lw-rt-data-page .lw-panel-page-eyebrow,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-page-eyebrow{font-size:.625rem;letter-spacing:.04em;text-transform:none}
.lw-panel--rt .lw-rt-data-page .lw-panel-page-title,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-page-title{margin-top:.25rem;font-size:1.125rem;font-weight:700}
.lw-panel--rt .lw-rt-data-page .lw-panel-page-lead,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-page-lead{margin-top:.375rem;font-size:.8125rem;line-height:1.45;max-width:36rem}
.lw-panel--rt .lw-rt-data-page .lw-panel-page-head--row,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-page-head--row{align-items:center}
.lw-panel--rt .lw-rt-data-page .lw-panel-actions,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-actions{margin:0;gap:.375rem}
.lw-panel--rt .lw-rt-data-page .lw-panel-stats,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-stats{gap:.5rem;margin-bottom:.875rem}
@media(min-width:640px){
.lw-panel--rt .lw-rt-data-page .lw-panel-stats,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-stats{grid-template-columns:repeat(3,1fr)}
}
.lw-panel--rt .lw-rt-data-page .lw-panel-stat,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-stat{padding:.625rem .75rem;border-color:var(--lw-border-soft);box-shadow:none}
.lw-panel--rt .lw-rt-data-page .lw-panel-stat-label,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-stat-label{font-size:.625rem;font-weight:500;text-transform:none;letter-spacing:normal}
.lw-panel--rt .lw-rt-data-page .lw-panel-stat-value,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-stat-value{margin-top:.25rem;font-size:1.125rem;font-weight:700}
.lw-panel--rt .lw-rt-data-page .lw-rt-data-status-tabs,
.lw-panel--kelurahan .lw-rt-data-page .lw-rt-data-status-tabs{margin-bottom:.75rem;padding-bottom:.375rem}
.lw-panel--rt .lw-rt-data-page .lw-rt-filter-tab,
.lw-panel--kelurahan .lw-rt-data-page .lw-rt-filter-tab{font-size:.75rem;padding:.3rem .625rem}
.lw-panel--rt .lw-rt-data-page .lw-rt-list-toolbar--compact.lw-rt-list-toolbar,
.lw-panel--kelurahan .lw-rt-data-page .lw-rt-list-toolbar--compact.lw-rt-list-toolbar{padding:.75rem .875rem}
.lw-panel--rt .lw-rt-data-page .lw-panel-filter-field label,
.lw-panel--kelurahan .lw-rt-data-page .lw-panel-filter-field label{font-size:.6875rem}
.lw-panel--rt .lw-rt-data-page .lw-rt-data-active-filters,
.lw-panel--kelurahan .lw-rt-data-page .lw-rt-data-active-filters{margin:0 0 .75rem;font-size:.75rem;color:var(--lw-text-muted)}
.lw-panel--rt .lw-rt-list-toolbar--compact .lw-rt-list-toolbar-stack,
.lw-panel--kelurahan .lw-rt-list-toolbar--compact .lw-rt-list-toolbar-stack{min-width:100%}
.lw-panel--rt .lw-rt-list-toolbar--compact.lw-rt-list-toolbar,
.lw-panel--kelurahan .lw-rt-list-toolbar--compact.lw-rt-list-toolbar{padding:.875rem 1rem}
.lw-panel--rt .lw-rt-list-toolbar--compact .lw-rt-list-toolbar-filters,
.lw-panel--kelurahan .lw-rt-list-toolbar--compact .lw-rt-list-toolbar-filters{align-items:flex-end;width:100%}
.lw-panel--rt .lw-panel-table-wrap:not(.lw-panel-table-wrap--wide){margin-bottom:0}
.lw-panel--rt .lw-panel-table-wrap:not(.lw-panel-table-wrap--wide) .lw-panel-table thead th{position:sticky;top:0;z-index:1;background:var(--lw-bg-muted);box-shadow:0 1px 0 var(--lw-border-soft)}
.lw-panel--rt .lw-panel-table-wrap:not(.lw-panel-table-wrap--wide) .lw-panel-table tbody tr:hover td{background:var(--lw-bg-accent-soft,#f0fdfa)}
.lw-panel--rt .lw-panel-table .lw-panel-table-actions{white-space:nowrap}
.lw-panel--rt .lw-panel-pagination,
.lw-panel--rt .lw-mt-2:has(.pagination),
.lw-panel--rt .lw-mt-4:has(.pagination){margin-top:1rem}
.lw-panel--rt .lw-panel-section-title{font-size:1rem;font-weight:700;color:var(--lw-accent-dark);margin:0 0 .75rem}
.lw-panel--rt .lw-panel-section-subtitle{margin:0 0 .5rem;font-size:.75rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--lw-text-muted)}
.lw-panel--rt .lw-rt-request-reference{margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid var(--lw-border-soft)}
.lw-panel--rt .lw-rt-request-reference-block+.lw-rt-request-reference-block{margin-top:1rem;padding-top:1rem;border-top:1px solid var(--lw-border-soft)}
.lw-panel--rt .lw-rt-request-reference-text{margin:0;font-size:.875rem;line-height:1.55;color:var(--lw-text-body);white-space:pre-wrap}
.lw-panel--rt .lw-rt-request-reference-empty{margin:0;padding:.75rem 1rem;font-size:.8125rem;line-height:1.45;color:var(--lw-text-muted);background:var(--lw-bg-muted);border:1px dashed var(--lw-border);border-radius:.5rem}
.lw-panel--rt .lw-rt-request-reference .lw-panel-dl{margin:0}
.lw-panel--rt .lw-panel-dl--reference .lw-panel-dl-row{display:grid;grid-template-columns:minmax(5.5rem,7rem) 1fr;gap:.25rem .75rem;padding:.45rem 0;border-bottom:1px solid var(--lw-border-soft)}
.lw-panel--rt .lw-panel-dl--reference .lw-panel-dl-row:last-child{border-bottom:none}
.lw-panel--rt .lw-panel-dl--reference dt{align-self:start;padding-top:.05rem}
.lw-panel--rt .lw-rt-request-reference .lw-panel-doc-list{gap:.625rem}
.lw-panel--rt .lw-rt-request-reference .lw-panel-doc-item{padding:.75rem 1rem;background:var(--lw-bg-muted);border:1px solid var(--lw-border-soft);border-radius:.5rem}
.lw-panel--rt .lw-rt-request-reference .lw-panel-doc-item-actions{margin-top:.35rem}
.lw-panel--rt .lw-rt-request-reference-thumb{display:inline-block;margin:.35rem 0 .5rem}
.lw-panel--rt .lw-rt-request-reference-thumb img{max-height:12rem;width:auto;border-radius:.5rem;border:1px solid var(--lw-border);vertical-align:middle}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail{margin-bottom:0}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__meta{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem 1.5rem;padding-bottom:1.25rem;margin-bottom:1.25rem;border-bottom:1px solid var(--lw-border-soft)}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__title{margin:0;font-size:1.125rem;font-weight:800;color:var(--lw-accent-dark);letter-spacing:-.02em}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__subtitle{margin:.2rem 0 0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.45}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__meta-chips{display:flex;flex-wrap:wrap;gap:.5rem .75rem;align-items:center}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__meta-item{display:inline-flex;flex-wrap:wrap;align-items:center;gap:.35rem .5rem;padding:.4rem .75rem;background:var(--lw-bg-muted);border:1px solid var(--lw-border-soft);border-radius:.5rem;font-size:.8125rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__meta-label{font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-text-muted)}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__meta-value{font-weight:600;color:var(--lw-text-strong)}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__body{display:flex;flex-direction:column;gap:1rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__card{padding:1rem 1.125rem;background:var(--lw-bg-muted);border:1px solid var(--lw-border-soft);border-radius:.625rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__card-title{margin:0 0 .875rem;font-size:.75rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--lw-text-muted)}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__detached{margin:0 0 .75rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__fields{display:grid;gap:.75rem 1rem;grid-template-columns:1fr}
@media(min-width:640px){:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__fields{grid-template-columns:repeat(2,minmax(0,1fr))}}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__field{display:flex;flex-direction:column;gap:.2rem;min-width:0}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__field--wide{grid-column:1/-1}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__field-label{font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-text-muted)}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__field-value{font-size:.875rem;font-weight:600;line-height:1.45;color:var(--lw-text-strong);word-break:break-word}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__field-value--mono{font-family:ui-monospace,monospace;font-size:.8125rem;font-weight:500}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__purpose{margin:0;padding:.875rem 1rem;font-size:.875rem;line-height:1.6;color:var(--lw-text-body);white-space:pre-wrap;background:var(--lw-bg-card);border:1px solid var(--lw-border-soft);border-left:3px solid var(--lw-accent);border-radius:.5rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__warn{margin-bottom:1rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__empty{padding:1.5rem 1rem;text-align:center;background:var(--lw-bg-card);border:1px dashed var(--lw-border);border-radius:.5rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__empty-title{margin:0;font-size:.875rem;font-weight:700;color:var(--lw-text-secondary)}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__empty-note{margin:.35rem 0 0;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.45}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-card{display:flex;flex-direction:column;overflow:hidden;background:var(--lw-bg-card);border:1px solid var(--lw-border-soft);border-radius:.625rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-preview{position:relative;height:9rem;background:var(--lw-bg-subtle);border-bottom:1px solid var(--lw-border-soft)}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-preview-link{display:block;width:100%;height:100%}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-preview-img{width:100%;height:100%;object-fit:cover;display:block}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-placeholder{display:flex;align-items:center;justify-content:center;width:100%;height:100%}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-placeholder-label{font-size:1.25rem;font-weight:800;letter-spacing:.08em;color:var(--lw-text-muted);opacity:.65}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-body{display:flex;flex-direction:column;flex:1;padding:.75rem .875rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-head{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem .5rem;margin-bottom:.35rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-type{font-size:.625rem}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-title{margin:0;font-size:.8125rem;font-weight:700;color:var(--lw-text-strong);line-height:1.35}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-meta{margin:0 0 .5rem;font-size:.6875rem;color:var(--lw-text-muted);line-height:1.4;word-break:break-all}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__doc-actions{display:flex;flex-wrap:wrap;gap:.35rem;margin-top:auto}
.lw-panel--rt .lw-letter-compose-service-fields{margin-bottom:1.25rem}
.lw-panel--rt .lw-letter-compose-service-fields-grid{display:grid;gap:.875rem 1rem}
@media(min-width:640px){.lw-panel--rt .lw-letter-compose-service-fields-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
.lw-panel--rt .lw-letter-compose-service-fields .lw-panel-field{margin:0}
.lw-panel--rt .lw-letter-compose-service-fields .lw-panel-field:has(textarea){grid-column:1/-1}
.lw-panel--rt .lw-letter-compose-field-input{width:100%;border:1px solid var(--lw-input-border);border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem}
.lw-panel--rt .lw-letter-compose-field-input:focus{outline:2px solid rgba(5,150,105,.25);outline-offset:1px;border-color:var(--lw-border-accent-strong)}
.lw-panel--rt .lw-panel-quick-grid{display:grid;gap:.75rem;grid-template-columns:repeat(auto-fill,minmax(14rem,1fr))}
.lw-panel--rt .lw-panel-prose-list{margin:0;padding-left:1.25rem;font-size:.8125rem;color:var(--lw-text-secondary);line-height:1.6}
.lw-panel--rt .lw-panel-prose-list li+li{margin-top:.35rem}
.lw-panel--rt .lw-panel-publications-nav{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}
.lw-panel--rt .lw-panel-btn--active{background:var(--lw-accent);color:#fff;border-color:var(--lw-accent)}
.lw-panel--rt .lw-panel-page-back{display:inline-block;margin-bottom:1rem;font-size:.875rem;font-weight:600;color:var(--lw-accent);text-decoration:none}
.lw-panel--rt .lw-panel-page-back:hover{text-decoration:underline}
.lw-panel--rt .lw-panel-card--full{max-width:none}
.lw-panel--rt .lw-panel-grid-2{display:grid;gap:1rem}
@media(min-width:900px){.lw-panel--rt .lw-panel-grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}}
/* RT page layout tokens */
.lw-panel--rt .lw-rt-page,
.lw-panel--kelurahan .lw-rt-page{display:flex;flex-direction:column;gap:1rem;min-width:0}
.lw-panel--rt .lw-rt-data-page,
.lw-panel--kelurahan .lw-rt-data-page{display:flex;flex-direction:column;gap:1rem;min-width:0}
.lw-panel--rt .lw-panel-stack{display:flex;flex-direction:column;gap:1rem;min-width:0}
.lw-panel--rt .lw-rt-review-actions{margin:0}
:is(.lw-panel--rt,.lw-panel--kelurahan,.lw-panel--admin) .lw-rt-application-detail__body .lw-rt-review-actions{margin-top:0}
.lw-panel--rt .lw-rt-review-actions__buttons{display:flex;flex-wrap:wrap;gap:.75rem;align-items:center}
.lw-panel--rt .lw-rt-reject-modal__card{max-width:32rem}
.lw-panel--rt .lw-rt-application-actions{margin-top:.25rem}
@media(min-width:900px){.lw-panel--rt .lw-rt-application-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}}
.lw-panel--rt .lw-panel-stack--sm{gap:.75rem}
.lw-panel--rt .lw-panel-stack .lw-panel-form--sidebar{margin-bottom:0}
.lw-panel--rt .lw-panel-field{margin-bottom:.875rem}
.lw-panel--rt .lw-panel-field-label{display:block;font-size:.8125rem;font-weight:600;color:var(--lw-text-secondary);margin-bottom:.35rem;line-height:1.35}
.lw-panel--rt .lw-panel-field-input,.lw-panel--rt .lw-panel-field select,.lw-panel--rt .lw-panel-field textarea{width:100%;border:1px solid var(--lw-input-border);border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;background:var(--lw-bg-card);color:var(--lw-text-body);box-sizing:border-box}
.lw-panel--rt .lw-panel-field-input:focus,.lw-panel--rt .lw-panel-field select:focus,.lw-panel--rt .lw-panel-field textarea:focus{outline:2px solid rgba(5,150,105,.25);outline-offset:1px;border-color:var(--lw-border-accent-strong)}
.lw-panel--rt .lw-form-error{margin:.25rem 0 0;font-size:.75rem;color:#dc2626;line-height:1.35}
.lw-panel--rt .is-hidden{display:none!important}
.lw-panel--rt .lw-panel-form-grid--labeled{display:grid;gap:.875rem 0;grid-template-columns:1fr}
.lw-panel--rt .lw-panel-form-grid--labeled>.lw-panel-field{margin-bottom:0}
.lw-panel--rt .lw-panel-form-grid--labeled>.lw-panel-field--span2{display:flex;flex-direction:column;gap:.35rem}
.lw-panel--rt .lw-panel-form--labeled .lw-panel-field--span2{display:flex;flex-direction:column;gap:.35rem}
.lw-panel--rt .lw-panel-form--labeled .lw-panel-field{margin-bottom:.875rem}
.lw-panel--rt .lw-panel-form--labeled .lw-panel-field:last-child{margin-bottom:0}
.lw-panel--rt .lw-panel-form--wide.lw-panel-form--labeled{max-width:min(100%,52rem)}
@media(min-width:640px){
.lw-panel--rt .lw-panel-form-grid--labeled>.lw-panel-field:not(.lw-panel-field--span2){display:grid;grid-template-columns:var(--lw-form-label-col) minmax(0,1fr);gap:.35rem 1rem;align-items:start}
.lw-panel--rt .lw-panel-form-grid--labeled>.lw-panel-field:not(.lw-panel-field--span2)>.lw-panel-field-label,
.lw-panel--rt .lw-panel-form-grid--labeled>.lw-panel-field:not(.lw-panel-field--span2)>label.lw-panel-field-label{margin-bottom:0;padding-top:.5rem;line-height:1.4;max-width:var(--lw-form-label-col);overflow-wrap:break-word}
.lw-panel--rt .lw-panel-form--labeled .lw-panel-field:not(.lw-panel-field--span2){display:grid;grid-template-columns:var(--lw-form-label-col) minmax(0,1fr);gap:.35rem 1rem;align-items:start;margin-bottom:.875rem}
.lw-panel--rt .lw-panel-form--labeled .lw-panel-field:not(.lw-panel-field--span2)>.lw-panel-field-label,
.lw-panel--rt .lw-panel-form--labeled .lw-panel-field:not(.lw-panel-field--span2)>label:first-child{margin-bottom:0;padding-top:.5rem;line-height:1.4;max-width:var(--lw-form-label-col);overflow-wrap:break-word}
.lw-panel--rt .lw-panel-form--labeled .lw-panel-field:not(.lw-panel-field--span2)>.lw-panel-field-hint,
.lw-panel--rt .lw-panel-form--labeled .lw-panel-field:not(.lw-panel-field--span2)>.lw-form-error{grid-column:2}
.lw-panel--rt .lw-panel-form-grid--labeled>.lw-panel-field:not(.lw-panel-field--span2)>.lw-panel-field-hint,
.lw-panel--rt .lw-panel-form-grid--labeled>.lw-panel-field:not(.lw-panel-field--span2)>.lw-form-error{grid-column:2}
.lw-panel--rt .lw-rt-reg-form.lw-panel-form--labeled.lw-panel-form--wide{max-width:100%}
.lw-panel--rt .lw-rt-reg-form.lw-panel-form--labeled .lw-panel-form-grid--labeled>.lw-panel-field,
.lw-panel--rt .lw-rt-reg-form.lw-panel-form--labeled.lw-panel-form--wide .lw-panel-field{display:flex;flex-direction:column;align-items:flex-start;gap:.35rem;margin-bottom:0;grid-template-columns:unset;width:100%;min-width:0}
.lw-panel--rt .lw-rt-reg-form .lw-panel-form-grid--labeled>.lw-panel-field--span2{align-items:flex-start}
.lw-panel--rt .lw-rt-reg-form .lw-panel-form-grid--labeled>.lw-panel-field>.lw-panel-field-label,
.lw-panel--rt .lw-rt-reg-form .lw-panel-form-grid--labeled>.lw-panel-field>label.lw-panel-field-label{display:inline-block;width:fit-content;max-width:100%;align-self:flex-start;padding-top:0;margin-bottom:0}
.lw-panel--rt .lw-rt-reg-form .lw-panel-field>.lw-panel-field-hint,
.lw-panel--rt .lw-rt-reg-form .lw-panel-field>.lw-form-error{width:100%;align-self:stretch;grid-column:auto}
}
.lw-panel--rt .lw-panel-table--rt-list{min-width:36rem;font-size:.8125rem}
@media(max-width:639px){.lw-panel--rt .lw-panel-table--rt-list{min-width:0;font-size:.75rem}}
.lw-panel--rt .lw-panel-table--rt-list .lw-rt-col-hide-sm{display:table-cell}
@media(max-width:639px){.lw-panel--rt .lw-panel-table--rt-list .lw-rt-col-hide-sm{display:none}}
.lw-panel--rt .lw-panel-table-actions{display:flex;flex-wrap:wrap;gap:.35rem .5rem;align-items:center;white-space:normal}
.lw-panel--rt .lw-panel-table-actions form{display:inline-flex;margin:0}
@media(max-width:639px){
.lw-panel--rt .lw-panel-page-head--row{flex-direction:column;align-items:stretch}
.lw-panel--rt .lw-panel-page-head--row .lw-panel-actions{width:100%;flex-direction:column;align-items:stretch}
.lw-panel--rt .lw-panel-page-head--row .lw-panel-actions .lw-panel-btn{width:100%;text-align:center;min-height:2.75rem;display:inline-flex;align-items:center;justify-content:center}
}
.lw-panel--rt .lw-panel-publication-preview{display:block;max-height:10rem;width:auto;max-width:100%;object-fit:cover;border-radius:.5rem;border:1px solid var(--lw-border);margin-bottom:.5rem}
.lw-panel--kelurahan .lw-kel-page{display:flex;flex-direction:column;gap:1rem;min-width:0}
.lw-kel-app-show{display:flex;flex-direction:column;gap:1rem;min-width:0}
.lw-kel-app-summary{display:grid;gap:.75rem;grid-template-columns:repeat(2,minmax(0,1fr));padding:1rem 1.125rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem}
@media(min-width:768px){.lw-kel-app-summary{grid-template-columns:repeat(4,minmax(0,1fr))}}
.lw-kel-app-summary__item{display:flex;flex-direction:column;gap:.35rem;min-width:0;padding:.5rem .65rem;background:var(--lw-bg-muted);border:1px solid var(--lw-border-soft);border-radius:.5rem}
.lw-kel-app-summary__label{font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-text-muted)}
.lw-kel-app-summary__value{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem .5rem;font-size:.8125rem;font-weight:600;color:var(--lw-text-strong);line-height:1.4}
.lw-kel-app-summary__letter-no{font-family:ui-monospace,monospace;font-size:.75rem;font-weight:500;color:var(--lw-accent-dark);word-break:break-all}
.lw-kel-app-detail__head{padding-bottom:1rem;margin-bottom:1rem;border-bottom:1px solid var(--lw-border-soft)}
.lw-kel-letter-card{padding:1rem 1.125rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:.75rem}
.lw-kel-letter-card--issued{border-color:#a7f3d0;background:linear-gradient(180deg,#ecfdf5 0%,var(--lw-bg-card) 4rem)}
.lw-kel-letter-card--pending{border-style:dashed;background:var(--lw-bg-muted)}
.lw-kel-letter-card__head{margin-bottom:.75rem}
.lw-kel-letter-card__title{margin:0;font-size:.75rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--lw-text-muted)}
.lw-kel-letter-card__number{margin:0 0 .5rem;font-family:ui-monospace,monospace;font-size:1.125rem;font-weight:700;line-height:1.35;color:var(--lw-accent-dark);word-break:break-all}
.lw-kel-letter-card__number--inline{font-size:.875rem;margin:0}
.lw-kel-letter-card__note{margin:0 0 .75rem;font-size:.8125rem;line-height:1.45;color:var(--lw-text-muted)}
.lw-kel-letter-card__meta{display:grid;gap:.5rem .75rem;margin:0}
@media(min-width:480px){.lw-kel-letter-card__meta{grid-template-columns:repeat(2,minmax(0,1fr))}}
.lw-kel-letter-card__meta-row{display:flex;flex-direction:column;gap:.15rem;min-width:0}
.lw-kel-letter-card__meta-row dt{margin:0;font-size:.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--lw-text-muted)}
.lw-kel-letter-card__meta-row dd{margin:0;font-size:.8125rem;font-weight:600;color:var(--lw-text-strong);line-height:1.4}
.lw-kel-letter-card__pending{margin:0;font-size:.875rem;font-weight:600;color:var(--lw-text-secondary)}
.lw-kel-letter-card__pending-meta{margin:.35rem 0 0;font-size:.8125rem;color:var(--lw-text-muted)}
.lw-panel--kelurahan .lw-kel-pop-toolbar-head{flex-direction:column;align-items:stretch}
@media(min-width:768px){.lw-panel--kelurahan .lw-kel-pop-toolbar-head{flex-direction:row;align-items:flex-start;justify-content:space-between}}
.lw-panel--admin .lw-admin-page{display:flex;flex-direction:column;gap:1rem;min-width:0}
.lw-panel-stats,.lw-admin-stats{display:grid;grid-template-columns:repeat(2,1fr);gap:.75rem;margin-bottom:1.5rem}
@media(min-width:640px){.lw-panel-stats--4,.lw-admin-stats{grid-template-columns:repeat(4,1fr)}}
.lw-panel-toolbar,.lw-admin-toolbar{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1rem;margin-bottom:1rem}
.lw-panel-toolbar-filters,.lw-admin-toolbar-filters{flex:1;min-width:min(100%,16rem);display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem}
.lw-panel-toolbar-action,.lw-admin-toolbar-action{flex-shrink:0;white-space:nowrap}
.lw-panel-filter-field,.lw-admin-filter-field{display:flex;flex-direction:column;gap:.3rem;min-width:10rem}
.lw-panel-filter-field label,.lw-admin-filter-field label{font-size:.75rem;font-weight:600;color:var(--lw-text-secondary)}
.lw-panel-filter-field input,.lw-panel-filter-field select,.lw-admin-filter-field input,.lw-admin-filter-field select{width:100%;border:1px solid var(--lw-input-border);border-radius:.5rem;padding:.45rem .65rem;font-size:.8125rem;background:var(--lw-bg-card)}
.lw-panel-filter-field--grow,.lw-admin-filter-field--grow{flex:1;min-width:12rem}
.lw-panel-filter-actions,.lw-admin-filter-actions{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
.lw-panel-empty,.lw-admin-empty{padding:2.5rem 1.5rem;text-align:center;background:var(--lw-bg-card);border:1px dashed var(--lw-border);border-radius:.75rem}
.lw-panel-empty-title,.lw-admin-empty-title{margin:0;font-size:.9375rem;font-weight:600;color:var(--lw-text-secondary)}
.lw-panel-empty-desc,.lw-admin-empty-desc{margin:.5rem 0 0;font-size:.8125rem;color:var(--lw-text-muted);max-width:24rem;margin-left:auto;margin-right:auto}
.lw-admin-role-badge{display:inline-block;padding:.2rem .55rem;font-size:.6875rem;font-weight:600;border-radius:9999px;line-height:1.3}
.lw-admin-role-badge--admin{background:#ede9fe;color:#5b21b6}
.lw-admin-role-badge--kelurahan{background:#dbeafe;color:#1e40af}
.lw-admin-role-badge--rt{background:#d1fae5;color:#065f46}
.lw-admin-role-badge--sekretaris{background:#cffafe;color:#0e7490}
.lw-admin-role-badge--warga{background:var(--lw-bg-subtle);color:var(--lw-text-secondary)}
.lw-admin-alert-list{display:flex;flex-direction:column;gap:.5rem;margin:0;padding:0;list-style:none}
.lw-admin-alert-item{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;padding:.75rem 1rem;background:var(--lw-stat-warn);border:1px solid #fcd34d;border-radius:.625rem;font-size:.8125rem;color:#92400e}
.lw-admin-alert-item a{font-weight:600;color:#b45309;text-decoration:none;white-space:nowrap}
.lw-admin-alert-item a:hover{text-decoration:underline}
.lw-panel-section-head,.lw-admin-section-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.75rem}
.lw-panel-nav-group,.lw-admin-nav-group{margin-bottom:.5rem}
.lw-panel-nav-group-label,.lw-admin-nav-group-label{margin:0 0 .25rem .625rem;font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(236,253,245,.55)}
.lw-panel-nav-link-inner,.lw-admin-nav-link-inner{display:inline-flex;align-items:center;gap:.5rem;min-width:0}
.lw-panel-nav-icon,.lw-admin-nav-icon{width:1.1875rem;height:1.1875rem;flex-shrink:0;opacity:.9}
.lw-panel-table-actions,.lw-admin-table-actions{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
.lw-panel-btn--sm{padding:.35rem .75rem;font-size:.75rem}
.lw-panel-btn--ghost{background:transparent;color:var(--lw-accent);border:1px solid var(--lw-border-accent-strong)}
.lw-panel-btn--ghost:hover{background:var(--lw-bg-accent-soft)}
.lw-panel-form-label-required{color:#dc2626;font-weight:700}
.lw-panel-form-actions{display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--lw-border-soft)}
.lw-panel-form-actions--flush{margin-top:0;padding-top:0;border-top:none}
.lw-panel-form-fieldset{border:none;margin:0 0 1.25rem;padding:0}
.lw-panel-form-legend{margin:0 0 .75rem;font-size:.875rem;font-weight:700;color:var(--lw-text-strong)}
.lw-panel-topbar-role{display:inline-block;margin-left:.5rem;padding:.15rem .5rem;font-size:.625rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--lw-accent);background:var(--lw-bg-accent-soft);border:1px solid var(--lw-border-accent-strong);border-radius:9999px;vertical-align:middle}
.lw-mt-3{margin-top:.75rem}

/* ——— Halaman Kegiatan (scoped) ——— */
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.lw-activities-page{display:flex;flex-direction:column;gap:0;min-width:0}
.lw-activities-hero{background:var(--lw-hero-bg);border-bottom:1px solid var(--lw-border-soft)}
.lw-activities-hero__inner{display:grid;gap:1.5rem;align-items:center;padding:1.5rem 0 1.75rem}
@media(min-width:768px){.lw-activities-hero__inner{grid-template-columns:1fr minmax(10rem,14rem);gap:2rem}}
.lw-activities-hero__eyebrow{display:inline-flex;align-items:center;gap:.5rem;margin:0 0 .625rem;font-size:.8125rem;font-weight:600;color:var(--lw-accent-text)}
.lw-activities-hero__eyebrow svg{flex-shrink:0;color:var(--lw-accent)}
.lw-activities-hero__title{margin:0;font-size:clamp(1.5rem,4vw,2rem);font-weight:800;line-height:1.2;color:var(--lw-text-strong);letter-spacing:-.02em}
.lw-activities-hero__lead{margin:.625rem 0 0;max-width:36rem;font-size:.9375rem;line-height:1.6;color:var(--lw-text-muted)}
.lw-activities-hero__illustration{display:flex;justify-content:center;align-items:center}
.lw-activities-hero__calendar-art{width:100%;max-width:11rem;height:auto;filter:drop-shadow(0 8px 24px rgba(15,118,110,.12))}
.lw-activities-body{padding:1.25rem 0 2rem}
.lw-activities-toolbar{display:flex;flex-direction:column;gap:.875rem;margin-bottom:1.25rem}
@media(min-width:768px){.lw-activities-toolbar{flex-direction:row;align-items:center;justify-content:space-between;gap:1rem}}
.lw-activities-filter{display:flex;flex-wrap:wrap;gap:.5rem}
.lw-activities-filter__chip{padding:.4375rem .875rem;border-radius:9999px;border:1px solid var(--lw-border-soft);background:var(--lw-bg-card);font-size:.8125rem;font-weight:600;color:var(--lw-text-secondary);cursor:pointer;transition:background .15s,border-color .15s,color .15s}
.lw-activities-filter__chip:hover{border-color:var(--lw-border-accent);color:var(--lw-accent-text)}
.lw-activities-filter__chip.is-active{background:linear-gradient(135deg,var(--lw-accent-dark),var(--lw-accent));border-color:var(--lw-accent-dark);color:#fff;box-shadow:0 4px 14px rgba(15,118,110,.2)}
.lw-activities-search{position:relative;display:flex;align-items:center;min-width:min(100%,16rem)}
.lw-activities-search__icon{position:absolute;left:.875rem;color:var(--lw-text-faint);pointer-events:none}
.lw-activities-search__input{width:100%;padding:.5625rem .875rem .5625rem 2.5rem;border-radius:.75rem;border:1px solid var(--lw-border-soft);background:var(--lw-bg-card);font-size:.875rem;color:var(--lw-text-body);box-shadow:var(--lw-shadow-sm)}
.lw-activities-search__input:focus{outline:2px solid var(--lw-border-accent);outline-offset:1px}
.lw-activities-layout{display:grid;gap:1.25rem;align-items:start}
@media(max-width:899px){.lw-activities-layout{grid-template-columns:1fr}}
@media(min-width:900px){.lw-activities-layout{grid-template-columns:minmax(0,1fr) minmax(16rem,22rem);gap:1.5rem}}
.lw-activities-event-list{display:flex;flex-direction:column;gap:.875rem}
.lw-activities-event-card{display:flex;gap:1rem;padding:1.125rem 1.25rem;border-radius:1rem;border:1px solid var(--lw-border-soft);background:var(--lw-bg-card);box-shadow:var(--lw-shadow-sm);transition:box-shadow .2s,border-color .2s}
.lw-activities-event-card:hover{box-shadow:var(--lw-shadow-md);border-color:var(--lw-border-accent)}
.lw-activities-event-card[hidden]{display:none!important}
.lw-activities-event-card__media{flex-shrink:0;width:7rem;border-radius:.75rem;overflow:hidden;border:1px solid var(--lw-border-soft);background:var(--lw-bg-subtle)}
@media(max-width:639px){.lw-activities-event-card__media{width:5.5rem}}
.lw-activities-event-card__photo{display:block;width:100%;aspect-ratio:4/3;object-fit:cover}
.lw-activities-event-card__body{flex:1;min-width:0}
.lw-activities-event-card__head{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:.5rem .75rem}
.lw-activities-event-card__title{margin:0;font-size:1rem;font-weight:700;color:var(--lw-text-strong);line-height:1.35}
.lw-activities-event-card__status{flex-shrink:0;padding:.2rem .55rem;border-radius:9999px;font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.lw-activities-event-card__status--hari_ini{background:#ecfdf5;color:#047857;border:1px solid #6ee7b7}
.lw-activities-event-card__status--akan_datang{background:#eff6ff;color:#1d4ed8;border:1px solid #93c5fd}
.lw-activities-event-card__status--selesai{background:#f8fafc;color:#64748b;border:1px solid #e2e8f0}
.lw-activities-event-card__meta{display:flex;flex-wrap:wrap;gap:.375rem 1rem;margin:.625rem 0 0;padding:0;list-style:none;font-size:.8125rem;color:var(--lw-text-muted);line-height:1.45}
.lw-activities-event-card__meta li{display:inline-flex;align-items:center;gap:.3rem}
.lw-activities-event-card__meta svg{flex-shrink:0;color:var(--lw-accent);opacity:.8}
.lw-activities-event-card__category{display:inline-block;margin-top:.625rem;padding:.25rem .6rem;border-radius:9999px;font-size:.6875rem;font-weight:700;color:var(--lw-accent);background:var(--lw-bg-accent-soft);border:1px solid var(--lw-border-accent-strong)}
.lw-activities-empty-filter{margin:1rem 0 0;padding:1rem 1.125rem;border-radius:.75rem;background:var(--lw-bg-muted);border:1px dashed var(--lw-border-accent);font-size:.875rem;color:var(--lw-text-muted);text-align:center}
.lw-activities-announce-panel{padding:1.25rem 1.125rem;border-radius:1rem;border:1px solid var(--lw-border-accent);background:var(--lw-bg-card);box-shadow:var(--lw-shadow-sm)}
@media(min-width:1024px){.lw-activities-announce-panel{position:sticky;top:5.5rem}}
.lw-activities-announce-panel__head{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:1rem}
.lw-activities-announce-panel__title{margin:0;font-size:1rem;font-weight:700;color:var(--lw-text-strong)}
.lw-activities-announce-panel__toggle{padding:0;border:none;background:none;font-size:.8125rem;font-weight:600;color:var(--lw-accent);cursor:pointer;text-decoration:none}
.lw-activities-announce-panel__toggle:hover{text-decoration:underline}
.lw-activities-announce-list{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:.75rem}
.lw-activities-announce-item.is-collapsed{display:none}
.lw-activities-announce-panel.is-expanded .lw-activities-announce-item.is-collapsed{display:block}
.lw-activities-announce-card{display:flex;gap:.75rem;padding:.875rem;border-radius:.75rem;border:1px solid var(--lw-border-soft);background:var(--lw-bg-muted)}
.lw-activities-announce-card__icon{flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;border-radius:9999px;background:var(--lw-bg-accent-soft);color:var(--lw-accent)}
.lw-activities-announce-card__body{flex:1;min-width:0}
.lw-activities-announce-card__title{margin:0;font-size:.875rem;font-weight:700;color:var(--lw-text-strong);line-height:1.35}
.lw-activities-announce-card__summary{margin:.35rem 0 0;font-size:.8125rem;line-height:1.5;color:var(--lw-text-muted);display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.lw-activities-announce-card__foot{display:flex;flex-wrap:wrap;gap:.35rem .75rem;margin-top:.5rem;font-size:.75rem;color:var(--lw-text-faint)}
.lw-activities-announce-card__rt{font-weight:600;color:var(--lw-accent-text)}
.lw-kegiatan-page .lw-kegiatan-page-hero.lw-page-hero{margin-bottom:0;box-shadow:var(--lw-shadow-sm)}
.lw-kegiatan-page .lw-public-section-stack{gap:var(--lw-section-gap)}
.lw-kegiatan-page .lw-page-subnav{margin-bottom:0;box-shadow:var(--lw-shadow-sm);background:var(--lw-bg-card-translucent)}
.lw-kegiatan-page .lw-kegiatan-panel{background:var(--lw-bg-card);border:1px solid var(--lw-border);border-radius:1rem;padding:1.25rem 1.125rem;box-shadow:var(--lw-shadow-sm)}
@media(min-width:640px){.lw-kegiatan-page .lw-kegiatan-panel{padding:1.5rem 1.375rem}}
.lw-kegiatan-page .lw-services-section{margin-top:0}
.lw-kegiatan-page .lw-pengumuman-section{padding-top:0;border-top:none}
.lw-kegiatan-page .lw-kegiatan-panel .lw-services-admin-intro{margin-bottom:0;padding:0;border:none;box-shadow:none;background:transparent;border-radius:0;gap:0}
.lw-kegiatan-page .lw-kegiatan-section-head{margin-bottom:.75rem;gap:.375rem}
.lw-kegiatan-page .lw-kegiatan-section-head .lw-section-tag,.lw-kegiatan-page .lw-kegiatan-section-head .lw-section-title,.lw-kegiatan-page .lw-kegiatan-section-head .lw-section-desc{margin:0}
.lw-kegiatan-page .lw-kegiatan-section-head .lw-section-title{line-height:1.3}
.lw-kegiatan-page .lw-kegiatan-section-head .lw-section-desc{max-width:40rem}
.lw-kegiatan-page .lw-section-tag{margin:0}
.lw-kegiatan-page .lw-section-title{margin:0;line-height:1.3}
.lw-kegiatan-page .lw-section-desc{margin:0;max-width:40rem}
.lw-kegiatan-empty{margin:1rem 0 0;padding:1rem 1.125rem;border-radius:.75rem;background:var(--lw-bg-muted);border:1px dashed var(--lw-border-accent);font-size:.875rem;line-height:1.55;color:var(--lw-text-muted);text-align:center}
.lw-kegiatan-page .lw-kegiatan-empty{margin-top:.5rem}
.lw-kegiatan-empty--compact{margin-top:.5rem;padding:.75rem 1rem;font-size:.8125rem}
.lw-kegiatan-page .lw-kegiatan-grid{gap:var(--lw-card-gap);width:100%}
@media(min-width:640px){.lw-kegiatan-page .lw-kegiatan-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:1024px){.lw-kegiatan-page .lw-kegiatan-grid{grid-template-columns:repeat(3,1fr)}}
.lw-kegiatan-page .lw-pengumuman-grid{gap:var(--lw-card-gap);width:100%}
@media(min-width:640px){.lw-kegiatan-page .lw-pengumuman-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:1024px){.lw-kegiatan-page .lw-pengumuman-grid{grid-template-columns:repeat(3,1fr)}}
.lw-kegiatan-page .lw-kegiatan-card{transition:box-shadow .2s,border-color .2s}
.lw-kegiatan-page .lw-kegiatan-card:hover{box-shadow:var(--lw-shadow-md);border-color:var(--lw-border-accent)}
.lw-kegiatan-page .lw-kegiatan-photo-wrap{display:block;overflow:hidden;line-height:0;background:var(--lw-bg-subtle)}
.lw-kegiatan-page .lw-kegiatan-photo{display:block;width:100%;max-width:100%;height:auto;aspect-ratio:auto;object-fit:unset}
.lw-kegiatan-page .lw-kegiatan-card-meta{display:flex;flex-wrap:wrap;align-items:center;row-gap:.2rem;column-gap:.35rem;margin-bottom:.25rem}
.lw-kegiatan-page .lw-kegiatan-card-meta>.lw-kegiatan-rt-badge,.lw-kegiatan-page .lw-kegiatan-card-meta>.lw-kegiatan-date,.lw-kegiatan-page .lw-kegiatan-card-meta>.lw-kegiatan-lokasi-inline{margin:0}
.lw-kegiatan-page .lw-kegiatan-card-meta .lw-kegiatan-date{display:inline-flex;align-items:center;gap:.2rem;margin:0;letter-spacing:.02em}
.lw-kegiatan-page .lw-kegiatan-lokasi-inline{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden;font-size:.75rem;color:var(--lw-text-secondary);line-height:1.35;max-width:100%}
.lw-kegiatan-page .lw-kegiatan-meta-icon{font-size:.7rem;line-height:1;opacity:.85;flex-shrink:0}
.lw-kegiatan-page .lw-kegiatan-card-inner{gap:.5rem;padding:1rem 1.125rem}
.lw-kegiatan-page .lw-kegiatan-card-name{font-size:1rem}
.lw-kegiatan-card-meta{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem .5rem}
.lw-kegiatan-card-meta .lw-kegiatan-date{display:inline-flex;align-items:center;gap:.2rem}
.lw-kegiatan-lokasi-inline{font-size:.75rem;color:var(--lw-text-secondary)}
.lw-kegiatan-meta-icon{font-size:.7rem}
.lw-kegiatan-page .lw-calendar-layout{display:grid;gap:1.25rem;margin-top:.5rem}
@media(min-width:900px){.lw-kegiatan-page .lw-calendar-layout{grid-template-columns:minmax(0,1.35fr) minmax(14rem,1fr);gap:1.5rem;align-items:start}}
.lw-kegiatan-page .lw-calendar-wrap{margin-top:0;padding:1rem;border-radius:.875rem;background:var(--lw-bg-muted);border:1px solid var(--lw-border-soft)}
.lw-kegiatan-page .lw-calendar-header{margin-bottom:.75rem}
.lw-kegiatan-page .lw-calendar-grid{gap:.35rem}
.lw-kegiatan-page .lw-calendar-day{position:relative;font-size:.8125rem;min-height:2.25rem;border-radius:.5rem}
.lw-kegiatan-page .lw-calendar-day--has-event::after{content:"";position:absolute;bottom:.2rem;left:50%;width:.35rem;height:.35rem;margin-left:-.175rem;border-radius:9999px;background:var(--lw-accent-bright)}
.lw-kegiatan-page .lw-calendar-day--selected{background:var(--lw-accent);color:#fff;border-color:var(--lw-accent)}
.lw-kegiatan-page .lw-calendar-day--selected.lw-calendar-day--has-event::after{background:#fff}
.lw-kegiatan-page .lw-calendar-day--today:not(.lw-calendar-day--selected){border-color:var(--lw-border-accent-strong);background:var(--lw-bg-accent-soft)}
.lw-kegiatan-page .lw-calendar-detail{margin-top:1rem;padding:1rem;border-radius:.75rem;background:var(--lw-bg-card);border:1px solid var(--lw-border-accent)}
.lw-kegiatan-page .lw-calendar-selected-label{margin:0 0 .5rem;font-size:.875rem;font-weight:600;color:var(--lw-text-strong)}
.lw-kegiatan-page .lw-calendar-events-list{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:.45rem}
.lw-kegiatan-page .lw-calendar-event-item{display:flex;flex-direction:column;gap:.1rem;font-size:.875rem;line-height:1.45;color:var(--lw-text-body)}
.lw-kegiatan-page .lw-calendar-event-meta{font-size:.8125rem;font-weight:500;color:var(--lw-text-muted)}
.lw-kegiatan-page .lw-calendar-day-empty{margin:0;font-size:.875rem;line-height:1.5;color:var(--lw-text-muted);text-align:center}
.lw-kegiatan-page .lw-calendar-global-empty{margin-top:1rem}
.lw-kegiatan-page .lw-calendar-aside{padding:1rem 1.125rem;border-radius:.875rem;background:var(--lw-bg-card);border:1px solid var(--lw-border);box-shadow:var(--lw-shadow-sm)}
.lw-kegiatan-page .lw-calendar-aside-title{margin:0 0 .75rem;font-size:.875rem;font-weight:700;color:var(--lw-accent-text)}
.lw-kegiatan-page .lw-calendar-upcoming-list{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:.5rem}
.lw-kegiatan-page .lw-calendar-upcoming-item{padding-bottom:.5rem;border-bottom:1px solid var(--lw-border-soft)}
.lw-kegiatan-page .lw-calendar-upcoming-item:last-child{padding-bottom:0;border-bottom:none}
.lw-kegiatan-page .lw-calendar-upcoming-date{display:block;font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:var(--lw-accent)}
.lw-kegiatan-page .lw-calendar-upcoming-title{margin:.15rem 0 0;font-size:.875rem;font-weight:700;color:var(--lw-text-strong);line-height:1.35}
.lw-kegiatan-page .lw-calendar-upcoming-meta{margin:.1rem 0 0;font-size:.75rem;color:var(--lw-text-muted);line-height:1.4}
.lw-kegiatan-page .lw-gallery-grid{gap:1rem;margin-top:.25rem}
.lw-kegiatan-page .lw-gallery-item{border:1px solid var(--lw-border);box-shadow:var(--lw-shadow-sm);transition:transform .2s,box-shadow .2s,border-color .2s}
.lw-kegiatan-page .lw-gallery-item:hover{transform:translateY(-2px);border-color:var(--lw-border-accent-strong);box-shadow:var(--lw-shadow-md)}
.lw-kegiatan-page .lw-gallery-caption{display:flex;flex-direction:column;align-items:flex-start;gap:.15rem;padding:.5rem .75rem .625rem}
.lw-kegiatan-page .lw-gallery-caption-title{display:block;font-size:.75rem;font-weight:600;line-height:1.35}
.lw-kegiatan-page .lw-gallery-date{display:block;font-size:.625rem;font-weight:500;opacity:.9}
.lw-kegiatan-page .lw-pengumuman-card--page{border-left:3px solid var(--lw-border-accent-strong);padding:1.125rem 1.25rem}
.lw-kegiatan-page .lw-pengumuman-card-meta{display:flex;flex-wrap:wrap;align-items:center;row-gap:.2rem;column-gap:.35rem;margin-bottom:.35rem}
.lw-kegiatan-page .lw-pengumuman-card-meta>.lw-kegiatan-rt-badge,.lw-kegiatan-page .lw-pengumuman-card-meta>.lw-kegiatan-date{margin:0}
.lw-kegiatan-page .lw-pengumuman-card-meta .lw-kegiatan-date{letter-spacing:.02em}
.lw-kegiatan-page .lw-pengumuman-card-inner{gap:.5rem}
.lw-kegiatan-page .lw-pengumuman-card-name{font-size:1rem}
@media(max-width:639px){
.lw-kegiatan-page .lw-calendar-day{min-height:2rem;font-size:.75rem}
.lw-kegiatan-page .lw-kegiatan-panel{padding:1.125rem 1rem}
}
</style>
