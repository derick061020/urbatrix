<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Makai Residences</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=2">
</head>

<body data-view="grid">


  <!-- MORE INFO MODAL — Figma 220:20041 (modal-tipologia) -->
  <div id="moreInfoModal" class="mt-overlay" style="display:none;">
    <div class="mt-backdrop" onclick="closeMoreInfo()"></div>
    <div class="mt-shell" role="dialog" aria-modal="true" aria-label="Unit details">

      <!-- HEADER -->
      <div class="mt-header">
        <div class="mt-header-left">
          <img src="/images/makai-logo.png" alt="Makai" class="mt-header-logo">
          <span class="mt-header-dot"></span>
          <span class="mt-header-unit">Unit <span id="modalUnitNum">A-101</span></span>
          <span id="modalStatusBadge" class="mt-badge-available">
            <span class="dot"></span><span id="modalStatusText">AVAILABLE</span>
          </span>
        </div>
        <div class="mt-header-right">
          <div class="mt-pill mt-pill-soft">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Last inquiry <b>2 hours ago</b> · Shortlisted by <b>7 others</b></span>
          </div>
          <div class="mt-pill mt-pill-wa">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12.05 21.785a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>
            <span>Contact Broker on WhatsApp</span>
          </div>
          <button class="mt-close" onclick="closeMoreInfo()" aria-label="Close">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>

      <div class="mt-body">

        <!-- LEFT — content panel -->
        <aside class="mt-left">
          <div class="mt-peopleview">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
            <span><b id="modalShortlistedCount">0</b> people shortlisted this unit</span>
          </div>

          <div class="mt-left-inner">

            <!-- Discount chip -->
            <span class="mt-discount-chip">Unlock $20,000 Discount</span>

            <!-- Price block -->
            <div class="mt-price-block">
              <div class="mt-price-row">
                <span class="mt-price" id="modalPrice">$450 000</span>
                <div class="mt-currency-toggle">
                  <button type="button" class="active" data-cur="USD">USD</button>
                  <button type="button" data-cur="EUR">EUR</button>
                  <button type="button" data-cur="CAD">CAD</button>
                  <button type="button" data-cur="MXN">MXN</button>
                </div>
              </div>
              <div class="mt-price-meta">
                <span class="muted">$450/sqft</span>
                <span class="sep"></span>
                <span class="success">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M7 14l5-5 5 5z" transform="rotate(180 12 12)"/></svg>
                  12% below Cap Cana avg.
                </span>
              </div>
              <div class="mt-price-meta">
                <span class="warning">Reserve from $5,000</span>
                <span class="sep"></span>
                <span class="muted">100% refundable</span>
              </div>
            </div>

            <!-- Description -->
            <p class="mt-desc" id="modalDesc">1st Floor &nbsp;·&nbsp; 1 Bed &amp; Family Room &nbsp;·&nbsp; SE &nbsp;·&nbsp; Lake Facing</p>

            <!-- Stats — top row (4 boxes) + bottom row (3 boxes) -->
            <div class="mt-stats-rows">
              <div class="mt-stats-row">
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatBed">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 14v4h20v-4a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3z"/><path d="M2 14V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v7"/><path d="M7 11V9h10v2"/></svg></div>
                  <div class="label">BED</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatBath">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6V4a2 2 0 0 1 4 0"/><path d="M2 11h20"/><path d="M5 11v6a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-6"/><line x1="6" y1="22" x2="6" y2="20"/><line x1="18" y1="22" x2="18" y2="20"/></svg></div>
                  <div class="label">BATH</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatPark">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14"/><path d="M5 17V9l1.5-4h11L19 9v8"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg></div>
                  <div class="label">PARK</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatPool">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg></div>
                  <div class="label">POOL</div>
                </div>
              </div>
              <div class="mt-stats-row">
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatInt">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" stroke-dasharray="2 2"/></svg></div>
                  <div class="label">INT M²</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatExt">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 8 3 3 8 3"/><polyline points="16 3 21 3 21 8"/><polyline points="21 16 21 21 16 21"/><polyline points="8 21 3 21 3 16"/></svg></div>
                  <div class="label">TERRACE M²</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatTotal">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V3h18"/><line x1="3" y1="9" x2="9" y2="9"/><line x1="3" y1="15" x2="9" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="9"/></svg></div>
                  <div class="label">TOTAL M²</div>
                </div>
              </div>
            </div>

            <div class="mt-divider"></div>

            <!-- For Investment / For Living toggle -->
            <div class="mt-buyer-toggle" role="tablist">
              <button type="button" class="active" data-buyer="investment">For Investment</button>
              <button type="button" data-buyer="living">For Living</button>
            </div>

            <!-- Financial table (For Investment) — pulled from DB; rows hidden when data is missing -->
            <div class="mt-fin-table mt-investment-only" id="mtFinTable">
              <div class="row">
                <div class="cell" id="modalRowLevies" style="display:none;">
                  <span class="k">HOA Levies</span>
                  <span class="v"><b id="modalLevies">—</b><i>/mo</i></span>
                </div>
                <div class="cell" id="modalRowRental" style="display:none;">
                  <span class="k">Est. Rental Income</span>
                  <span class="v success"><b id="modalRental">—</b><i>/mo</i></span>
                </div>
              </div>
              <div class="row">
                <div class="cell" id="modalRowFees" style="display:none;">
                  <span class="k">Monthly Fees</span>
                  <span class="v"><b id="modalFees">—</b><i>/mo</i></span>
                </div>
                <div class="cell" id="modalRowRates" style="display:none;">
                  <span class="k">Rates</span>
                  <span class="v"><b id="modalRates">—</b><i>/mo</i></span>
                </div>
              </div>
            </div>

            <!-- Financial table (For Living) — costs only, no rental income -->
            <div class="mt-fin-table mt-living-only" id="mtFinTableLiving">
              <div class="row">
                <div class="cell" id="modalRowLeviesL" style="display:none;">
                  <span class="k">HOA Levies</span>
                  <span class="v"><b id="modalLeviesL">—</b><i>/mo</i></span>
                </div>
                <div class="cell" id="modalRowRatesL" style="display:none;">
                  <span class="k">Rates</span>
                  <span class="v"><b id="modalRatesL">—</b><i>/mo</i></span>
                </div>
              </div>
              <div class="row">
                <div class="cell" id="modalRowFeesL" style="display:none;">
                  <span class="k">Monthly Fees</span>
                  <span class="v"><b id="modalFeesL">—</b><i>/mo</i></span>
                </div>
                <div class="cell" id="modalRowTotalCost" style="display:none;">
                  <span class="k">Total Monthly Cost</span>
                  <span class="v"><b id="modalTotalCost">—</b><i>/mo</i></span>
                </div>
              </div>
            </div>

            <!-- For-living extras: amenities + walk score + school -->
            <div class="mt-living-extras mt-living-only">
              <div class="mt-living-row" id="modalRowAmen" style="display:none;">
                <span class="ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21l9-7 9 7"/><path d="M5 10V21h14V10"/><polyline points="2 10 12 3 22 10"/></svg></span>
                <span class="txt" id="modalAmenities">—</span>
              </div>
              <div class="mt-living-row" id="modalRowWalk" style="display:none;">
                <span class="ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13" cy="4" r="2"/><path d="M11 8l-4 6 4 3 0 5"/><path d="M14 13l3-1"/></svg></span>
                <span class="txt"><b id="modalWalkScore">—</b><span class="muted"> walkability score</span></span>
              </div>
              <div class="mt-living-row" id="modalRowSchool" style="display:none;">
                <span class="ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10l-10-5L2 10l10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></span>
                <span class="txt" id="modalSchool">—</span>
              </div>
            </div>

            <!-- Projected value highlight (investment) -->
            <div class="mt-projected mt-investment-only" id="modalProjected" style="display:none;">
              <div class="mt-projected-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg>
              </div>
              <div>
                <p class="mt-projected-label">PROJECTED VALUE AT DELIVERY</p>
                <div class="mt-projected-row">
                  <span class="now" id="modalProjectedNow">$0 today</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                  <span class="future" id="modalProjectedFuture">—</span>
                  <span class="hint" id="modalProjectedHint">—</span>
                </div>
              </div>
            </div>

            <!-- Investment commentary -->
            <div class="mt-compare mt-investment-only" id="modalCompare" style="display:none;">
              <span class="bullet"></span>
              <span id="modalCompareText">—</span>
            </div>

            <!-- For-investment longform description -->
            <p class="mt-section-text mt-investment-only" id="modalInvestmentText" style="display:none;"></p>

            <!-- For-living longform description -->
            <p class="mt-section-text mt-living-only" id="modalLivingText" style="display:none;"></p>

            <div class="mt-divider"></div>

            <!-- Floor plan downloads -->
            <p class="mt-section-label">Floor Plan Downloads</p>
            <div class="mt-fancy-row">
              <button type="button" class="mt-fancy-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                With Measurements
              </button>
              <button type="button" class="mt-fancy-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Without Measurements
              </button>
            </div>

            <!-- Advisor card -->
            <div class="mt-advisor">
              <div class="mt-advisor-left">
                <div class="mt-avatar">
                  <span class="mt-avatar-letter">CM</span>
                  <span class="mt-avatar-status"></span>
                </div>
                <div>
                  <div class="mt-advisor-name">Carlos Méndez</div>
                  <div class="mt-advisor-status">Available right now</div>
                </div>
              </div>
              <button type="button" class="mt-advisor-chat">
                Chat
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
              </button>
            </div>
          </div>

          <!-- Sticky bottom -->
          <div class="mt-cta-row">
            <button id="modalReserveBtn" type="button" class="mt-btn-secondary" onclick="openReservePage(currentOpenUnit)">Reserve Online</button>
            <button type="button" class="mt-btn-primary" onclick="openVideoCall()">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/></svg>
              Book Video Call
            </button>
          </div>
        </aside>

        <!-- RIGHT — gallery panel -->
        <section class="mt-right">

          <!-- Tabs -->
          <div class="mt-tabs">
            <button type="button" class="mt-tab">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              ADD TO LIST
            </button>
            <button type="button" class="mt-tab mt-tab-middle">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
              DISCLAIMER
            </button>
            <button type="button" class="mt-tab" onclick="sharePropertyPdf()">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
              SHARE
            </button>
          </div>

          <!-- Image -->
          <div class="mt-gallery">
            <img id="modalMainImg" src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FA_16_LA_MA_AXO_T1A_HR%2F1773673791087%2Ffull.webp" alt="Unit" class="mt-gallery-img">

            <!-- Dimensions toggle -->
            <div class="mt-dim-toggle">
              <button type="button" class="active">NO DIMENSIONS</button>
              <button type="button">WITH DIMENSIONS</button>
            </div>

            <!-- Arrows -->
            <button class="mt-arrow mt-arrow-left" type="button" onclick="prevModalImg()" aria-label="Previous">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button class="mt-arrow mt-arrow-right" type="button" onclick="nextModalImg()" aria-label="Next">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>

            <span class="mt-counter" id="modalImgCounter">1 / 4</span>
          </div>

          <!-- Thumbs -->
          <div class="mt-thumbs" id="mtThumbs">
            <button type="button" class="mt-thumb active" data-idx="0">
              <img src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FA_16_LA_MA_AXO_T1A_HR%2F1773673791087%2Ffull.webp" alt="">
            </button>
            <button type="button" class="mt-thumb" data-idx="1">
              <img src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FB_Makai_Cards_Unit_Layout_111-T1A%2F1773673791087%2Ffull.webp" alt="">
            </button>
            <button type="button" class="mt-thumb" data-idx="2">
              <img src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FC_Makai_Floorplans_First_Floor_111%2F1773673791087%2Ffull.webp" alt="">
            </button>
            <button type="button" class="mt-thumb" data-idx="3">
              <img src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FD_Makai_Floorplans_Second_Floor_111%2F1773673791087%2Ffull.webp" alt="">
            </button>
          </div>
        </section>
      </div><!-- /.mt-body -->
    </div><!-- /.mt-shell -->
  </div><!-- /#moreInfoModal -->


  <!-- ADVISOR VIDEO CALL MODAL -->
  <div id="advisorModal" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:1200;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,0.55);padding:1rem;">
    <div style="background:white;border-radius:1rem;max-width:380px;width:100%;padding:1.5rem;position:relative;box-shadow:0 20px 50px rgba(0,0,0,0.3);">
      <button onclick="closeAdvisorVideoCall()" style="position:absolute;top:0.5rem;right:0.5rem;background:transparent;border:none;font-size:1.5rem;color:rgb(98,84,65);cursor:pointer;line-height:1;">×</button>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:0.5rem;">
        <span class="advisor-dot" style="width:8px;height:8px;border-radius:50%;background:rgb(34,197,94);display:inline-block;"></span>
        <span style="font-size:0.7rem;color:rgb(34,150,80);font-weight:600;">Asesor disponible · Próxima ventana en 2 h</span>
      </div>
      <h3 style="margin:0 0 0.25rem 0;font-size:1.1rem;font-weight:700;color:rgb(98,84,65);">Agendar Videollamada</h3>
      <p style="margin:0 0 1rem 0;font-size:0.75rem;color:rgb(98,84,65);opacity:0.75;">Te contactamos para confirmar el horario.</p>
      <form onsubmit="return submitAdvisorVideoCall(event)">
        <input type="hidden" name="unit_id" id="advisorModalUnitId" value="">
        <input type="text" name="name" placeholder="Nombre completo" required style="width:100%;padding:0.55rem 0.75rem;border:1px solid rgb(218,211,200);border-radius:0.5rem;margin-bottom:0.5rem;font-size:0.85rem;color:rgb(37,32,24);outline:none;">
        <input type="email" name="email" placeholder="Email" required style="width:100%;padding:0.55rem 0.75rem;border:1px solid rgb(218,211,200);border-radius:0.5rem;margin-bottom:0.5rem;font-size:0.85rem;color:rgb(37,32,24);outline:none;">
        <input type="tel" name="phone" placeholder="Teléfono (con prefijo)" required style="width:100%;padding:0.55rem 0.75rem;border:1px solid rgb(218,211,200);border-radius:0.5rem;margin-bottom:0.5rem;font-size:0.85rem;color:rgb(37,32,24);outline:none;">
        <input type="text" name="preferred_time" placeholder="Horario preferido (ej. mañana 10:00 GMT-5)" required style="width:100%;padding:0.55rem 0.75rem;border:1px solid rgb(218,211,200);border-radius:0.5rem;margin-bottom:0.75rem;font-size:0.85rem;color:rgb(37,32,24);outline:none;">
        <button type="submit" style="width:100%;height:2.5rem;border-radius:0.5rem;background:rgb(102,123,106);color:white;border:none;font-size:0.8rem;font-weight:700;letter-spacing:0.05em;cursor:pointer;">CONFIRMAR SOLICITUD</button>
      </form>
    </div>
  </div>

  <!-- RESERVE PAGE (shown when RESERVE is clicked) -->
  <div id="reservePage" style="display: none; position: fixed; top: 0px; left: 0px; width: 100%; height: 100%; z-index: 2000; overflow-y: auto; background: rgb(239, 235, 230);">
    <!-- Navbar -->
    <nav style="display:flex;width:100%;padding:0.5rem;position:sticky;top:0;z-index:100;background:rgb(251,250,246);height:112px;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
      <div style="display:flex;flex-direction:row;width:33.33%;justify-content:flex-start;height:100%;padding:0 0.5rem;align-items:center;">
        <a href="#" style="display:flex;align-items:center;height:100%;padding:0.5rem 0;text-decoration:none;" onclick="event.preventDefault();">
          <img src="/images/makai-logo.png" alt="logo" class="logo-img" style="max-height:88px;max-width:220px;object-fit:contain;">
        </a>
      </div>
      <div style="display:flex;flex-direction:row;width:33.33%;justify-content:center;align-items:center;">
        <span style="text-transform:uppercase;text-align:center;font-weight:700;font-size:1.25rem;letter-spacing:0.08em;color:rgb(99,124,105);">{{ $soldCount ?? 0 }} OF {{ $totalUnits ?? 0 }} UNITS SOLD</span>
      </div>
      <div style="display:flex;flex-direction:row;width:33.33%;justify-content:flex-end;align-items:center;gap:0.75rem;padding-right:1rem;">
        <span style="text-transform:uppercase;font-weight:700;letter-spacing:0.08em;font-size:0.875rem;cursor:pointer;color:rgb(99,124,105);">MENU</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="rgb(99,124,105)" stroke-width="2.5" style="cursor:pointer;">
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="rgb(99,124,105)" style="cursor:pointer;">
          <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"></path>
        </svg>
      </div>
    </nav>

    <!-- Page Content -->
    <!-- STEP 1: Form -->
    <div id="reserveStep1" style="display: none;">
      <!-- Top Banner -->
      <div style="width:100%;display:flex;flex-direction:column;align-items:center;padding:2rem;background:rgb(218,211,200);">
        <button onclick="closeReservePage()" style="display:flex;flex-direction:row;padding:0.5rem 1rem;color:white;text-transform:uppercase;letter-spacing:0.08em;background:rgb(157,137,108);border:none;border-radius:0.375rem;cursor:pointer;align-items:center;gap:0.5rem;font-size:0.875rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"></path>
          </svg>
          BACK TO PRICE LIST
        </button>
        <p style="margin-top:1.25rem;text-align:center;font-size:1.875rem;font-weight:600;text-transform:uppercase;color:rgb(98,84,65);">RESERVATION AGREEMENT FORM</p>
        <div style="margin-top:1.25rem;text-align:center;font-weight:600;padding:0.25rem 1rem;color:rgb(98,84,65);font-size:0.9rem;max-width:900px;">
          To secure your reservation we require $2000 reservation deposit. Only once this has been paid will your unit be reserved. Your reservation would need to be secured within 30 days by paying the first deposit and signing the Promise of Sale document. This deposit will be deducted from will be deducted from the 25% purchase deposit.
        </div>
      </div>

      <!-- Form Area -->
      <div style="display:flex;flex-direction:column;align-items:center;padding:1.25rem;background:white;min-height:60vh;">
        <div style="display:flex;flex-direction:column;justify-content:center;align-items:center;margin:0 auto;">
          <p style="font-size:1.125rem;font-weight:600;color:rgb(98,84,65);">Makai Residences <span style="font-weight:400;">Unit 111</span></p>

          <!-- Form Container -->
          <div style="margin-top:0.75rem;padding:1.25rem;background:rgb(243,244,246);border-radius:0.5rem;min-width:min(650px,90vw);">
            <form id="reservationForm" onsubmit="goToReserveStep2(event)">
              <!-- Hidden unit ID field -->
              <input type="hidden" id="unitId" name="unit_id" value="">
              
              <!-- Name Row -->
              <div style="display:flex;flex-direction:row;gap:0.25rem;margin-bottom:0.75rem;">
                <div style="width:50%;">
                  <label style="font-size:0.75rem;font-weight:600;padding:0 0.25rem;color:rgb(98,84,65);">First name</label>
                  <div style="position:relative;margin-top:0.25rem;">
                    <svg style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:rgb(150,140,130);" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"></path>
                    </svg>
                    <input type="text" name="first_name" placeholder="First name" required style="width:100%;height:2.75rem;padding:0.5rem 0.75rem 0.5rem 2.25rem;border-radius:0.375rem;border:1px solid rgb(198,186,169);background:white;font-size:0.875rem;color:rgb(37,32,24);">
                  </div>
                </div>
                <div style="width:50%;">
                  <label style="font-size:0.75rem;font-weight:600;padding:0 0.25rem;color:rgb(98,84,65);">Last name</label>
                  <div style="position:relative;margin-top:0.25rem;">
                    <svg style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:rgb(150,140,130);" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"></path>
                    </svg>
                    <input type="text" name="last_name" placeholder="Last name" required style="width:100%;height:2.75rem;padding:0.5rem 0.75rem 0.5rem 2.25rem;border-radius:0.375rem;border:1px solid rgb(198,186,169);background:white;font-size:0.875rem;color:rgb(37,32,24);">
                  </div>
                </div>
              </div>
              <!-- Email -->
              <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem;font-weight:600;padding:0 0.25rem;color:rgb(98,84,65);">Email</label>
                <div style="position:relative;margin-top:0.25rem;">
                  <svg style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:rgb(150,140,130);" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"></path>
                  </svg>
                  <input type="email" name="email" placeholder="Email address" required style="width:100%;height:2.75rem;padding:0.5rem 0.75rem 0.5rem 2.25rem;border-radius:0.375rem;border:1px solid rgb(198,186,169);background:white;font-size:0.875rem;color:rgb(37,32,24);">
                </div>
              </div>
              <!-- Contact Number -->
              <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem;font-weight:600;padding:0 0.25rem;color:rgb(98,84,65);">Contact Number</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.25rem;margin-top:0.25rem;">
                  <div style="display:flex;align-items:center;height:2.75rem;border-radius:0.375rem;background:white;border:1px solid rgb(198,186,169);padding:0 0.75rem;gap:0.5rem;">
                    <span style="font-size:1.2rem;">âº</span>
                    <select name="country" required style="border:none;background:transparent;font-size:0.875rem;color:rgb(37,32,24);flex:1;outline:none;">
                      <option value="United States">United States (+1)</option>
                      <option value="Dominican Republic">Dominican Republic (+1)</option>
                      <option value="Peru">Peru (+51)</option>
                      <option value="Colombia">Colombia (+57)</option>
                      <option value="Mexico">Mexico (+52)</option>
                      <option value="Spain">Spain (+34)</option>
                      <option value="United Kingdom">United Kingdom (+44)</option>
                    </select>
                  </div>
                  <div style="display:flex;align-items:center;height:2.75rem;border-radius:0.375rem;background:white;border:1px solid rgb(198,186,169);">
                    <span style="padding:0 0.5rem;color:rgb(150,140,130);font-size:0.875rem;border-right:1px solid rgb(198,186,169);">+1</span>
                    <input type="tel" name="phone" placeholder="Phone number" required style="flex:1;height:100%;padding:0.5rem 0.75rem;border:none;background:transparent;font-size:0.875rem;color:rgb(37,32,24);outline:none;">
                  </div>
                </div>
              </div>
              <!-- Terms Checkbox -->
              <div style="padding-left:0.5rem;margin:1.25rem 0;">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem;color:rgb(98,84,65);">
                  <input type="checkbox" style="width:1.25rem;height:1.25rem;accent-color:rgb(102,123,106);">
                  Accept Terms and Conditions
                </label>
              </div>
              <!-- Reserve Button -->
              <div style="margin-top:0.5rem;">
                <button type="submit" style="width:100%;height:3rem;border-radius:0.75rem;background:rgb(102,123,106);color:white;border:none;font-size:0.875rem;font-weight:700;letter-spacing:0.08em;cursor:pointer;">RESERVE</button>
              </div>
              <!-- Timer -->
              <div style="margin-top:0.75rem;font-size:0.875rem;color:rgb(98,84,65);">
                *Please note you have <b id="reserveTimer1" style="color:rgb(102,123,106);">08:52</b> minutes to complete your reservation.
              </div>
            </form>
          </div>
        </div>

        <!-- T&C Section -->
        <div style="margin-top:2.5rem;text-align:left;max-width:min(650px,90vw);padding:0 1.25rem;color:rgb(98,84,65);">
          <h3 style="font-size:1rem;font-weight:700;margin-bottom:0.5rem;">Terms &amp; Conditions</h3>
          <p style="font-size:0.875rem;margin:0.25rem 0;">A unit is only reserved once Makai has received the proof of payment of the reservation deposit.</p>
          <p style="font-size:0.875rem;margin:0.25rem 0;">The reservation deposit will be deducted from the 25% purchase deposit.</p>
          <p style="font-size:0.875rem;margin:0.25rem 0;">Your reservation would need to be secured within 30 days by paying the first deposit and signing the Promise of Sale document.</p>
          <p style="font-size:0.875rem;margin:0.25rem 0;">Completion of this purchase can be made by anyone that is authorised by the client.</p>
        </div>
      </div>
    </div>

    <!-- STEP 2: Review & Confirm -->
    <div id="reserveStep2" style="display: none;">
      <!-- Top Banner (same as step 1) -->
      <div style="width:100%;display:flex;flex-direction:column;align-items:center;padding:2rem;background:rgb(218,211,200);">
        <button onclick="backToStep1()" style="display:flex;flex-direction:row;padding:0.5rem 1rem;color:white;text-transform:uppercase;letter-spacing:0.08em;background:rgb(157,137,108);border:none;border-radius:0.375rem;cursor:pointer;align-items:center;gap:0.5rem;font-size:0.875rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"></path>
          </svg>
          BACK TO PRICE LIST
        </button>
        <p style="margin-top:1.25rem;text-align:center;font-size:1.875rem;font-weight:600;text-transform:uppercase;color:rgb(98,84,65);">RESERVATION AGREEMENT FORM</p>
        <div style="margin-top:1.25rem;text-align:center;font-weight:600;padding:0.25rem 1rem;color:rgb(98,84,65);font-size:0.9rem;max-width:900px;">
          To secure your reservation we require $2000 reservation deposit. Only once this has been paid will your unit be reserved. Your reservation would need to be secured within 30 days by paying the first deposit and signing the Promise of Sale document.
        </div>
      </div>

      <div style="display:flex;flex-direction:column;align-items:center;padding:1.25rem;background:white;min-height:60vh;">
        <p style="font-size:1.125rem;font-weight:600;color:rgb(98,84,65);margin-bottom:1rem;">Makai Residences <span style="font-weight:400;">Unit 111</span></p>

        <div style="min-width:min(650px,90vw);padding:1.25rem;background:rgb(243,244,246);border-radius:0.5rem;">
          <!-- Unit Specs Row -->
          <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:center;width:100%;margin-top:2.5rem;">
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:0.75rem;padding:0 1.25rem;">
              <svg fill="rgb(98,84,65)" width="22" height="22" viewBox="0 0 24 24">
                <path d="M20 10V7A2 2 0 0 0 18 5H6A2 2 0 0 0 4 7V10A2 2 0 0 0 2 12V17H3.33L4 19H5L5.67 17H18.33L19 19H20L20.67 17H22V12A2 2 0 0 0 20 10M13 7H18V10H13M6 7H11V10H6M20 15H4V12H20Z"></path>
              </svg>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">Bedrooms</span>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">1</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:0.75rem;padding:0 1.25rem;">
              <svg fill="rgb(98,84,65)" width="22" height="22" viewBox="0 0 24 24">
                <path d="M19,12H5V10H19V12M17.92,9H6.08C6.5,6.5 8.5,4.5 11,4.08V2H13V4.08C15.5,4.5 17.5,6.5 17.92,9Z"></path>
              </svg>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">Bathrooms</span>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">2</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:0.75rem;padding:0 1.25rem;">
              <svg fill="rgb(98,84,65)" width="22" height="22" viewBox="0 0 24 24">
                <path d="M5,11L6.5,6.5H17.5L19,11M17.5,16A1.5,1.5 0 0,1 16,14.5A1.5,1.5 0 0,1 17.5,13A1.5,1.5 0 0,1 19,14.5A1.5,1.5 0 0,1 17.5,16M6.5,16A1.5,1.5 0 0,1 5,14.5A1.5,1.5 0 0,1 6.5,13A1.5,1.5 0 0,1 8,14.5A1.5,1.5 0 0,1 6.5,16M18.92,6C18.72,5.42 18.16,5 17.5,5H6.5C5.84,5 5.28,5.42 5.08,6L3,12V20A1,1 0 0,0 4,21H5A1,1 0 0,0 6,20V19H18V20A1,1 0 0,0 19,21H20A1,1 0 0,0 21,20V12L18.92,6Z"></path>
              </svg>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">Parking Spots</span>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">1</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:0.75rem;padding:0 1.25rem;">
              <svg fill="rgb(98,84,65)" width="22" height="22" viewBox="0 0 24 24">
                <path d="M20.79,13.95L18.46,14.57L16.46,13.44V10.56L18.46,9.43L20.79,10.05L21.31,8.12L19.54,7.65L20,5.88L18.07,5.36L17.45,7.69L15.45,8.82L13,7.38V5.12L14.71,3.41L13.29,2L12,3.29L10.71,2L9.29,3.41L11,5.12V7.38L8.5,8.82L6.5,7.69L5.92,5.36L4,5.88L4.47,7.65L2.7,8.12L3.22,10.05L5.55,9.43L7.55,10.56V13.45L5.55,14.58L3.22,13.96L2.7,15.89L4.47,16.36L4,18.12L5.93,18.64L6.55,16.31L8.55,15.18L11,16.62V18.88L9.29,20.59L10.71,22L12,20.71L13.29,22L14.7,20.59L13,18.88V16.62L15.5,15.17L17.5,16.3L18.12,18.63L20,18.12L19.53,16.35L21.3,15.88L20.79,13.95Z"></path>
              </svg>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">Aircon</span>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">Yes</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:0.75rem;padding:0 1.25rem;">
              <svg fill="rgb(98,84,65)" width="22" height="22" viewBox="0 0 24 24">
                <path d="M7,21H9V19H7M11,21H13V19H11M19,15H9V5H19M19,3H9C7.89,3 7,3.89 7,5V15A2,2 0 0,0 9,17H14L18,17H19A2,2 0 0,0 21,15V5C21,3.89 20.1,3 19,3M15,21H17V19H15M3,9H5V7H3M5,21V19H3A2,2 0 0,0 5,21M3,13H5V11H3M3,17A2,2 0 0,0 5,19V17H3Z"></path>
              </svg>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">Internal Area</span>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">959 sqft</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:0.75rem;padding:0 1.25rem;">
              <svg fill="rgb(98,84,65)" width="22" height="22" viewBox="0 0 24 24">
                <path d="M15,17H17V15H15M15,5H17V3H15M5,7H3V19A2,2 0 0,0 5,21H17V19H5M19,17A2,2 0 0,0 21,15H19M19,9H21V7H19M19,13H21V11H19M9,17V15H7A2,2 0 0,0 9,17M13,3H11V5H13M19,3V5H21C21,3.89 20.1,3 19,3M13,15H11V17H13M9,3C7.89,3 7,3.89 7,5H9M9,11H7V13H9M9,7H7V9H9V7Z"></path>
              </svg>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">External Area</span>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">207 sqft</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:0.75rem;padding:0 1.25rem;">
              <svg fill="rgb(98,84,65)" width="22" height="22" viewBox="0 0 24 24">
                <path d="M3,5V21H9V19.5H7V18H9V16.5H5V15H9V13.5H7V12H9V10.5H5V9H9V5H10.5V9H12V7H13.5V9H15V5H16.5V9H18V7H19.5V9H21V3H5A2,2 0 0,0 3,5Z"></path>
              </svg>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">Total Area</span>
              <span style="font-weight:600;font-size:0.8rem;color:rgb(98,84,65);">1166 sqft</span>
            </div>
          </div>

          <!-- Total Price -->
          <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;margin-top:1.25rem;">
            <div style="width:83.33%;display:flex;flex-direction:column;align-items:center;justify-content:center;">
              <label style="display:flex;width:100%;text-align:left;font-weight:600;font-size:0.875rem;color:rgb(98,84,65);">Total Purchase Price</label>
              <span style="display:block;font-weight:800;font-size:1.125rem;color:rgb(180,134,72);">$431 000</span>
            </div>
          </div>

          <!-- Buttons -->
          <div style="width:100%;display:flex;flex-direction:row;justify-content:center;margin-top:1.25rem;gap:0.75rem;">
            <div style="width:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;">
              <button onclick="window.location.href='/form'" style="width:100%;height:3rem;border-radius:0.75rem;background:rgb(102,123,106);color:white;border:none;font-size:0.875rem;font-weight:700;letter-spacing:0.08em;cursor:pointer;">PROCEED</button>
            </div>
          </div>
          <button onclick="closeReservePage()" style="width:100%;height:3rem;border-radius:0.75rem;margin-top:0.75rem;background:transparent;color:rgb(239,68,68);border:1px solid rgb(239,68,68);font-size:0.875rem;font-weight:600;cursor:pointer;">CANCEL RESERVATION</button>

          <!-- Timer -->
          <div style="margin-top:0.75rem;font-size:0.875rem;color:rgb(98,84,65);text-align:center;">
            *Please note you have <b id="reserveTimer2" style="color:rgb(102,123,106);">08:52</b> minutes to complete your reservation.
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 3: Payment -->
    <div id="reserveStep3" style="display: none;">
      <div style="width:100%;display:flex;flex-direction:column;align-items:center;padding:2rem;background:rgb(218,211,200);">
        <p style="text-align:center;font-size:1.875rem;font-weight:600;text-transform:uppercase;color:rgb(98,84,65);">RESERVATION AGREEMENT FORM</p>
      </div>

      <div style="display:flex;flex-direction:column;align-items:center;padding:1.25rem;background:white;min-height:60vh;">
        <div style="min-width:min(650px,90vw);padding:1.25rem;background:rgb(243,244,246);border-radius:0.5rem;text-align:center;">
          <!-- Countdown Timer Circle -->
          <div style="margin:0 auto;position:relative;width:200px;height:200px;">
            <svg style="transform:scaleX(-1);" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" width="200" height="200">
              <g>
                <circle style="fill:none;stroke:none;" cx="50" cy="50" r="45"></circle>
                <path stroke-dasharray="251 283" style="stroke-width:7px;stroke-linecap:round;transform:rotate(90deg);transform-origin:center center;transition:1s linear;fill-rule:nonzero;stroke:rgb(65,184,131);fill:none;" d="M 50,50 m -45,0 a 45,45 0 1,0 90,0 a 45,45 0 1,0 -90,0" id="timerPath"></path>
                <circle style="fill:none;stroke:gray;stroke-width:7px;" cx="50" cy="50" r="45"></circle>
              </g>
            </svg>
            <div style="position:absolute;width:200px;height:200px;top:0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:rgb(98,84,65);" id="timerLabel">
              <div style="display:flex;align-items:center;">
                <span id="timerMin3">08</span>
                <span>:</span>
                <span id="timerSec3">52</span>
              </div>
            </div>
          </div>

          <p style="margin-top:0.75rem;font-size:0.875rem;color:rgb(98,84,65);">Please complete payment within the time limit.</p>

          <!-- PROCEED TO FINISH -->
          <button onclick="closeReservePage()" style="width:100%;height:3rem;border-radius:0.75rem;margin-top:1.5rem;background:rgb(102,123,106);color:white;border:none;font-size:0.875rem;font-weight:700;letter-spacing:0.08em;cursor:pointer;">PROCEED TO FINISH</button>

          <!-- CANCEL -->
          <button onclick="closeReservePage()" style="width:100%;height:3rem;border-radius:0.75rem;margin-top:0.75rem;background:transparent;color:rgb(239,68,68);border:1px solid rgb(239,68,68);font-size:0.875rem;font-weight:600;cursor:pointer;">CANCEL RESERVATION</button>

          <!-- Timer text -->
          <div style="margin-top:0.75rem;font-size:0.875rem;color:rgb(98,84,65);">
            *Please note you have <b id="reserveTimer3" style="color:rgb(102,123,106);">08:52</b> minutes to complete your reservation.
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer style="background:white;font-size:0.875rem;padding:0.5rem 1rem;color:rgb(98,84,65);">
      <div style="display:flex;justify-content:space-between;width:100%;flex-wrap:wrap;gap:0.5rem;">
        <div>© 2026 LaunchBase - All Rights Reserved</div>
        <div style="display:flex;gap:0.5rem;">
          <a href="#" style="color:rgb(98,84,65);">Privacy Policy</a>
          <p style="margin:0 0 0 0.5rem;">Platform Version: 1.427.0</p>
        </div>
      </div>
    </footer>
  </div>

  <!-- MAIN PAGE -->
  <div id="mainPage">
    <!-- Navbar -->
    <nav style="display:flex;align-items:center;justify-content:center;width:100%;padding:16px 24px 12px;position:fixed;top:0;left:0;right:0;z-index:100;background:transparent;pointer-events:none;">
      <div style="pointer-events:auto;display:flex;align-items:center;justify-content:space-between;gap:1rem;width:100%;max-width:1280px;height:60px;padding:8px 8px 8px 12px;background:#ffffff;border-radius:9999px;box-shadow:0 5px 5px rgba(0,0,0,0.04),0 18px 16px rgba(0,0,0,0.05),0 41px 22.5px rgba(0,0,0,0.04),0 73px 30px rgba(0,0,0,0.02);">
        <!-- LEFT: Logo + animated project selector -->
        <div class="logo-section" style="position:relative;display:flex;align-items:center;gap:8px;flex-shrink:0;min-width:200px;max-width:300px;">
          <div class="logo-container" style="display:flex;align-items:center;">
            <a href="#" onclick="return false;" style="display:flex;align-items:center;text-decoration:none;height:44px;padding:0 4px;border-radius:9999px;">
              <img src="/images/makai-logo.png" alt="logo" class="logo-img" style="height:32px;width:auto;max-width:160px;object-fit:contain;">
            </a>
          </div>
          <button type="button" id="projectsToggle" class="logo-trigger" onclick="toggleProjects()" aria-label="Switch project" aria-expanded="false">
            <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>

          <!-- PROJECT SELECTOR DROPDOWN -->
          <div class="project-selector" id="projectsDropdown" role="menu" aria-label="Projects">
            <button type="button" class="project-card active" data-project="makai" onclick="selectProject('Makai')" aria-label="Makai Residences" role="menuitem">
              <img src="/images/projects/makai.png" alt="Makai Residences">
            </button>
            <button type="button" class="project-card" data-project="naviva" onclick="selectProject('Naviva')" aria-label="Naviva" role="menuitem">
              <img src="/images/projects/naviva.png" alt="Naviva">
            </button>
            <button type="button" class="project-card" data-project="liv" onclick="selectProject('Liv')" aria-label="Liv" role="menuitem">
              <img src="/images/projects/liv.png" alt="Liv">
            </button>
          </div>
        </div>

        <!-- CENTER: Units sold + online users -->
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;flex:0 1 auto;min-width:0;">
          <span style="font-family:'Poppins',sans-serif;font-weight:700;font-size:14px;line-height:20px;letter-spacing:1.12px;color:var(--brand);text-align:center;white-space:nowrap;text-transform:uppercase;">{{ $soldCount ?? 0 }} OF {{ $totalUnits ?? 0 }} UNITS SOLD</span>
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="display:inline-block;width:6px;height:6px;background:#db5858;border-radius:50%;box-shadow:0 0 6px rgba(219,88,88,0.6);animation:pulse 1.5s infinite;"></span>
            <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:10px;line-height:20px;letter-spacing:0.2px;color:#db5858;white-space:nowrap;text-transform:uppercase;"><span data-active-users>1</span> User Online Right Now</span>
          </div>
        </div>
        <style>
          @keyframes pulse {

            0%,
            100% {
              opacity: 1;
              transform: scale(1);
            }

            50% {
              opacity: 0.4;
              transform: scale(0.7);
            }
          }
        </style>

        <!-- RIGHT: Saved counter + Avatar + Hamburger -->
        <div style="position:relative;display:flex;align-items:center;gap:12px;flex-shrink:0;">
          <a href="{{ auth()->check() ? route('dashboard.guardados') : route('login') }}" aria-label="Saved units" style="display:inline-flex;align-items:center;gap:4px;padding:0;background:transparent;border:none;cursor:pointer;border-radius:9999px;text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#a3a3a3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <span style="font-family:'Poppins',sans-serif;font-weight:500;font-size:12px;color:#a3a3a3;letter-spacing:-0.072px;white-space:nowrap;" data-saved-count>Guardados ({{ count($wishlistIds ?? []) }})</span>
          </a>

          <span aria-hidden="true" style="display:inline-block;width:1px;height:28px;background:#ebebeb;flex-shrink:0;"></span>

          <!-- Profile container with dropdown -->
          <div style="position:relative;">
            <button type="button" onclick="toggleProfileMenu()" aria-label="Profile" style="display:inline-flex;align-items:center;gap:6px;padding:0 0 0 6px;background:transparent;border:none;cursor:pointer;border-radius:9999px;">
              <span style="display:flex;flex-direction:column;align-items:flex-end;gap:2px;line-height:1;">
                <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:12px;color:var(--brand);">{{ auth()->check() ? explode(' ', auth()->user()->name)[0] : 'Samuel' }}</span>
                <span style="font-family:'Poppins',sans-serif;font-weight:500;font-size:9px;color:#99a0ae;letter-spacing:0.72px;text-transform:uppercase;">{{ auth()->check() && (auth()->user()->role ?? '') === 'admin' ? 'Admin' : 'Agente' }}</span>
              </span>
              @if(auth()->check() && auth()->user()->avatar)
                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" style="display:inline-block;width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;" aria-hidden="true">
              @else
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:var(--brand);color:white;font-family:'Poppins',sans-serif;font-weight:600;font-size:14px;flex-shrink:0;" aria-hidden="true">
                  {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'S' }}
                </span>
              @endif
            </button>

            <!-- PROFILE DROPDOWN -->
            <div class="menu-dropdown-container" id="profileDropdown">
              
              <!-- User Info Section -->
              <div style="display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;background:white;border-radius:10px;width:100%;flex-shrink:0;">
                <div style="position:relative;border-radius:999px;width:40px;height:40px;flex-shrink:0;overflow:hidden;">
                  @if(auth()->check() && auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" style="position:absolute;width:100%;height:100%;object-fit:cover;border-radius:999px;" />
                  @else
                    <span style="position:absolute;display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%;background:var(--brand);color:white;font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;border-radius:999px;">
                      {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'S' }}
                    </span>
                  @endif
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;justify-content:center;flex:1;min-width:0;">
                  <div style="font-family:'Poppins',sans-serif;font-weight:600;font-size:14px;color:#171717;letter-spacing:-0.084px;white-space:nowrap;">{{ auth()->check() ? auth()->user()->name : 'Samuel Urbina' }}</div>
                  <div style="font-family:'Poppins',sans-serif;font-weight:500;font-size:12px;color:#a3a3a3;min-width:max-content;">{{ auth()->check() ? auth()->user()->email : 'samuelurbi@gmail.com' }}</div>
                </div>
                <div style="background:white;border:1px solid #ebebeb;border-radius:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;padding:4px;flex-shrink:0;box-shadow:0px 1px 2px 0px rgba(10,13,20,0.03);">
                  <div style="display:flex;align-items:center;justify-content:center;padding:0 4px;flex-shrink:0;">
                    <div style="font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;white-space:nowrap;">USD</div>
                  </div>
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Language Toggle -->
              <div style="display:flex;flex-direction:column;align-items:flex-start;padding:8px 0;position:relative;width:100%;flex-shrink:0;">
                <div style="background:#f2f5f8;display:flex;gap:4px;align-items:center;justify-content:center;overflow:hidden;padding:4px;border-radius:12px;width:308px;position:relative;">
                  <div style="position:absolute;background:white;height:32px;left:4px;border-radius:8px;top:4px;width:148px;" id="lang-indicator"></div>
                  <button onclick="setLanguage('es')" id="lang-es" style="display:flex;gap:6px;align-items:center;justify-content:center;padding:6px;border-radius:8px;flex:1;min-width:0;position:relative;z-index:1;background:transparent;border:none;cursor:pointer;">
                    <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;border-radius:50%;">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="20" height="20" rx="10" fill="#AA151B"/>
                        <rect y="6.67" width="20" height="6.66" fill="#F1BF00"/>
                      </svg>
                    </div>
                    <div style="display:flex;flex-direction:column;justify-content:center;font-family:'Poppins',sans-serif;font-weight:600;font-size:12px;color:#525866;letter-spacing:-0.072px;white-space:nowrap;flex-shrink:0;">
                      <span style="line-height:20px;">Español</span>
                    </div>
                  </button>
                  <button onclick="setLanguage('en')" id="lang-en" style="display:flex;gap:8px;align-items:center;justify-content:center;padding:6px;border-radius:8px;flex:1;min-width:0;position:relative;z-index:1;background:transparent;border:none;cursor:pointer;opacity:0.52;">
                    <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;border-radius:50%;">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="20" height="20" rx="10" fill="#B22234"/>
                        <rect y="1.54" width="20" height="1.54" fill="white"/>
                        <rect y="4.62" width="20" height="1.54" fill="white"/>
                        <rect y="7.69" width="20" height="1.54" fill="white"/>
                        <rect y="10.77" width="20" height="1.54" fill="white"/>
                        <rect y="13.85" width="20" height="1.54" fill="white"/>
                        <rect y="16.92" width="20" height="1.54" fill="white"/>
                        <rect width="8.46" height="10.77" fill="#3C3B6E"/>
                      </svg>
                    </div>
                    <div style="display:flex;flex-direction:column;justify-content:center;font-family:'Poppins',sans-serif;font-weight:600;font-size:12px;color:#717784;letter-spacing:-0.072px;white-space:nowrap;flex-shrink:0;">
                      <span style="line-height:20px;">English</span>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Menu Items -->
              <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.crm.dashboard') : route('dashboard') }}" style="text-decoration:none;display:block;width:100%">
                <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <rect x="3" y="3" width="7" height="7"></rect>
                      <rect x="14" y="3" width="7" height="7"></rect>
                      <rect x="14" y="14" width="7" height="7"></rect>
                      <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                  </div>
                  <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Dashboard</div>
                </div>
              </a>

              <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.profile.edit') : route('dashboard.profile.edit') }}" style="text-decoration:none;display:block;width:100%">
                <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                      <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                  </div>
                  <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">My Profile</div>
                </div>
              </a>

              <a href="{{ auth()->check() ? route('dashboard.guardados') : route('login') }}" style="text-decoration:none;display:block;width:100%">
                <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                  </div>
                  <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Wishlist</div>
                </div>
              </a>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Contact Agent</div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Tech Support</div>
              </div>

              <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.crm.avance-obra') : route('dashboard.progress') }}" style="text-decoration:none;display:block;width:100%">
                <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                  </div>
                  <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Construction Progress</div>
                </div>
              </a>

              <!-- Divider -->
              <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;">
                <div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Main Website</div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">FAQs</div>
              </div>

              <!-- Divider -->
              <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;">
                <div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Brochure</div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Floor Plans</div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">ROIs</div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Specifications</div>
              </div>

              <!-- Divider -->
              <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;">
                <div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div>
              </div>

              <!-- Sign Out -->
              <form method="POST" action="/logout" style="margin:0;width:100%;flex-shrink:0;">
                @csrf
                <button type="submit" class="logout-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;border:none;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#dc2626;">
                      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                      <polyline points="16 17 21 12 16 7"></polyline>
                      <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                  </div>
                  <div style="flex:1;text-align: start;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">Sign out</div>
                </button>
              </form>

              <!-- Footer -->
              <div style="background:white;display:flex;align-items:center;overflow:hidden;padding:8px;width:100%;flex-shrink:0;">
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:12px;color:#a3a3a3;line-height:16px;">v.1.0.1 · Terms & Conditions</div>
              </div>

              <!-- Inner shadow effect -->
              <div style="position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:none;border-radius:inherit;box-shadow:inset 0px -1px 1px -0.5px rgba(23,23,23,0.06);"></div>
            </div>
          </div>

          <button type="button" onclick="toggleMenu()" aria-label="Menu" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:transparent;border:none;border-radius:9999px;cursor:pointer;padding:0;">
            <span aria-hidden="true" style="position:relative;display:inline-block;width:20px;height:20px;">
              <span style="position:absolute;left:0;top:4px;width:20px;height:2px;border-radius:2px;background:var(--brand);"></span>
              <span style="position:absolute;right:0;top:9px;width:15px;height:2px;border-radius:2px;background:var(--brand);"></span>
              <span style="position:absolute;right:0;top:14px;width:10px;height:2px;border-radius:2px;background:var(--brand);"></span>
            </span>
          </button>

        </div><!-- /right -->
      </div><!-- /pill -->
    </nav>

    </div><!-- /right -->
      </div><!-- /pill -->
    </nav>

    <!-- Hero -->
    <div class="fg-hero" id="hero" data-active="makai">
      <img class="fg-hero-layer fg-hero-sky" src="/images/hero/SKY.png" alt="" aria-hidden="true">

      <span class="fg-hero-text" data-project="makai"  aria-hidden="true">MAKAI</span>
      <span class="fg-hero-text" data-project="naviva" aria-hidden="true">NAVIVA</span>
      <span class="fg-hero-text" data-project="liv"    aria-hidden="true">LIV</span>

      <img class="fg-hero-building" data-project="makai"  src="/images/hero/MAKAI.png"  alt="Makai Residences">
      <img class="fg-hero-building" data-project="naviva" src="/images/hero/NAVIVA.png" alt="Naviva Residences">
      <img class="fg-hero-building" data-project="liv"    src="/images/hero/LIV.png"    alt="Liv Residences">

      <img class="fg-hero-layer fg-hero-clouds" src="/images/hero/clouds.png" alt="" aria-hidden="true">
    </div>
    <div class="fg-hero-spacer" aria-hidden="true"></div>

    <!-- Main Content -->
    <div id="main-unit-reserve-list" style="min-height:100vh;background:#f2f5f8;">

      <!-- Grid/List/Planta Toggle -->
      <div class="fg-toggle-bar">
        <div class="fg-toggle-container" role="tablist" aria-label="View mode">
          <div class="fg-toggle" data-node-id="171:10199">
            <button type="button" class="active" data-view="grid" role="tab" aria-selected="true">
              <div class="fg-icon-grid">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="7" height="7"></rect>
                  <rect x="14" y="3" width="7" height="7"></rect>
                  <rect x="3" y="14" width="7" height="7"></rect>
                  <rect x="14" y="14" width="7" height="7"></rect>
                </svg>
              </div>
              <span>Grid</span>
            </button>
            <button type="button" data-view="list" role="tab" aria-selected="false">
              <div class="fg-icon-list">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="8" y1="6" x2="21" y2="6"></line>
                  <line x1="8" y1="12" x2="21" y2="12"></line>
                  <line x1="8" y1="18" x2="21" y2="18"></line>
                  <line x1="4" y1="6" x2="4" y2="6"></line>
                  <line x1="4" y1="12" x2="4" y2="12"></line>
                  <line x1="4" y1="18" x2="4" y2="18"></line>
                </svg>
              </div>
              <span>List</span>
            </button>
            <div class="fg-toggle-bg-active" aria-hidden="true"></div>
          </div>

          <!-- Floating Map-Pin (Planta) Button -->
          <div class="fg-location-button">
            <button type="button" class="fg-location-btn" data-view="plan" role="tab" aria-selected="false" aria-label="Planta view">
              <div class="fg-icon-location">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Filter Bar — placed below the view toggle, visible only in Grid view -->
      <div class="fg-filter-bar" data-grid-only>
        <div class="fg-filters-left">
          <label class="fg-search">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#a3a3a3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="7"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" placeholder="Unit No.">
          </label>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('price')">
              <span id="priceLabel">Price</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="priceDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:170px;padding:12px;">
              <div style="margin-bottom:8px;">
                <label style="font-family:'Poppins',sans-serif;font-size:12px;color:#5c5c5c;font-weight:500;display:block;margin-bottom:4px;">Min Price</label>
                <input type="number" id="minPrice" placeholder="0" style="width:100%;padding:6px 8px;border:1px solid #ebebeb;border-radius:6px;font-size:14px;font-family:'Poppins',sans-serif;">
              </div>
              <div style="margin-bottom:8px;">
                <label style="font-family:'Poppins',sans-serif;font-size:12px;color:#5c5c5c;font-weight:500;display:block;margin-bottom:4px;">Max Price</label>
                <input type="number" id="maxPrice" placeholder="1000000" style="width:100%;padding:6px 8px;border:1px solid #ebebeb;border-radius:6px;font-size:14px;font-family:'Poppins',sans-serif;">
              </div>
              <button onclick="applyPriceFilter()" style="width:100%;padding:8px;background:var(--brand);color:white;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;font-family:'Poppins',sans-serif;">Apply</button>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('floor')">
              <span id="floorLabel">Floor</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="floorDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:120px;padding:12px;">
              <div style="max-height:220px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="Ground" onchange="applyFloorFilter()"> Ground</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="1st" onchange="applyFloorFilter()"> 1st</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="2nd" onchange="applyFloorFilter()"> 2nd</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="3rd" onchange="applyFloorFilter()"> 3rd</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="4th" onchange="applyFloorFilter()"> 4th</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="Penthouse" onchange="applyFloorFilter()"> Penthouse</label>
              </div>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('type')">
              <span id="typeLabel">Unit Type</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="typeDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:140px;padding:12px;">
              <div style="max-height:220px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="1 Bed" onchange="applyTypeFilter()"> 1 Bed</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="2 Bed" onchange="applyTypeFilter()"> 2 Bed</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="3 Bed" onchange="applyTypeFilter()"> 3 Bed</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="Studio" onchange="applyTypeFilter()"> Studio</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="Penthouse" onchange="applyTypeFilter()"> Penthouse</label>
              </div>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('direction')">
              <span id="directionLabel">Direction</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="directionDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:120px;padding:12px;">
              <div style="max-height:220px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="N" onchange="applyDirectionFilter()"> North</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="S" onchange="applyDirectionFilter()"> South</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="E" onchange="applyDirectionFilter()"> East</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="W" onchange="applyDirectionFilter()"> West</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="NE" onchange="applyDirectionFilter()"> NE</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="NW" onchange="applyDirectionFilter()"> NW</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="SE" onchange="applyDirectionFilter()"> SE</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="SW" onchange="applyDirectionFilter()"> SW</label>
              </div>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('outlook')">
              <span id="outlookLabel">Outlook</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="outlookDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:140px;padding:12px;">
              <div style="max-height:220px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="Lake" onchange="applyOutlookFilter()"> Lake</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="Garden" onchange="applyOutlookFilter()"> Garden</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="Pool" onchange="applyOutlookFilter()"> Pool</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="Ocean" onchange="applyOutlookFilter()"> Ocean</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="City" onchange="applyOutlookFilter()"> City</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="Mountain" onchange="applyOutlookFilter()"> Mountain</label>
              </div>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('sort')">
              <span id="sortLabel">Sort</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="sortDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:160px;padding:12px;">
              <div style="max-height:240px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="price-asc" onchange="applySortFilter()"> Price: Low to High</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="price-desc" onchange="applySortFilter()"> Price: High to Low</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="size-asc" onchange="applySortFilter()"> Size: Small to Large</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="size-desc" onchange="applySortFilter()"> Size: Large to Small</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="bedrooms-asc" onchange="applySortFilter()"> Bedrooms: Low to High</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="bedrooms-desc" onchange="applySortFilter()"> Bedrooms: High to Low</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="custom_id" onchange="applySortFilter()" checked> Unit Number</label>
              </div>
            </div>
          </div>

          <button class="fg-filter-icon" type="button" aria-label="Reset filters" title="Reset filters" onclick="if(typeof resetFilters==='function'){resetFilters();}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="1 4 1 10 7 10"></polyline>
              <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
            </svg>
          </button>
        </div>

        <button class="fg-pill-matches" type="button">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
          </svg>
          102 Matches
        </button>
      </div>
      <!-- Cards Grid -->
      <div class="fg-units-grid" style="padding-top:10px">
        @foreach($units as $unit)
        @php
          $st = strtolower($unit->status ?? '');
          $isSold      = $st === 'sold';
          $isReserved  = $st === 'reserved';
          $isPending   = $st === 'pending';
          $isHighDem   = !empty($unit->is_high_demand) || ($unit->demand_level ?? '') === 'high';
          $isSecond    = !empty($unit->is_second_chance);
          $hasDiscount = !empty($unit->discount) && $unit->discount > 0;
          $cardCls = 'fg-card';
          if ($isSold)         $cardCls .= ' is-sold';
          elseif ($isReserved) $cardCls .= ' is-reserved';
          elseif ($isPending)  $cardCls .= ' is-pending';
          elseif ($isSecond)   $cardCls .= ' is-second-chance';
          elseif ($isHighDem)  $cardCls .= ' is-high-demand';
          $unitId = $unit->custom_id ?? $unit->id;
          $shortlistedCount = (int) ($unit->shortlisted_count ?? 0);
        @endphp
        <!--- unit - start  ---->
        <div class="{{ $cardCls }}">
          <div class="fg-card-inner">

            <!-- Image area -->
            <div class="fg-card-img">
              @if($unit->images->isNotEmpty())
                <img src="{{ $unit->images->first()->path }}" alt="{{ $unitId }}" onerror="this.style.display='none'">
              @else
                <div class="fg-card-img-noimage">No Image Available</div>
              @endif

              <!-- Top row: status badge (left) + ADD TO LIST (right) -->
              <div class="fg-chip-row">
                @if($isHighDem)
                  <span class="fg-status-badge high-demand">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 23a7 7 0 0 1-7-7c0-2 1-3 1-3 0 1 1 2 2 2 0-3 2-5 2-8 0-2-1-3-1-3 4 0 8 4 8 9 1-1 2-2 2-4 2 1 3 4 3 7a7 7 0 0 1-7 7z"/></svg>
                    HIGH DEMAND
                  </span>
                @elseif($isPending)
                  <span class="fg-status-badge pending">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    PENDING
                  </span>
                @elseif($isSecond)
                  <span class="fg-status-badge second">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    2ND CHANCE
                  </span>
                @else
                  <span></span>
                @endif

                @php $isFav = in_array($unit->id, $wishlistIds ?? []); @endphp
                <button type="button"
                        class="fg-add-to-list {{ $isFav ? 'is-fav' : '' }}"
                        aria-label="Add to list"
                        aria-pressed="{{ $isFav ? 'true' : 'false' }}"
                        data-wishlist-toggle data-unit-id="{{ $unit->id }}"
                        title="Shortlisted by {{ $unit->shortlisted_count ?? 0 }} other">
                  <span class="heart">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="{{ $isFav ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                  </span>
                  <span class="text">
                    <span class="label">{{ $isFav ? 'Saved' : 'Add to list' }}</span>
                    <span class="meta">Shortlisted by <span data-unit-count="{{ $unit->id }}">{{ $unit->shortlisted_count ?? 0 }}</span> other</span>
                  </span>
                </button>
              </div>

              <!-- Gold "RESERVE FROM $5000" banner -->
              <div class="fg-reserve-banner">Reserve from $5000</div>

              @if($isSold)
                <!-- SOLD overlay (Figma 125:5048) -->
                <div class="fg-sold-badge"><span>SOLD</span></div>
              @endif
            </div>

            <!-- Body -->
            <div class="fg-card-body">

              <!-- Head: name + ROI, subtitle, divider, price, discount -->
              <div class="fg-card-head">
                <div class="fg-card-title-row">
                  <span class="name">{{ $unitId }}</span>
                  @if(!empty($unit->roi))
                    <span class="roi">{{ $unit->roi }}% ROI</span>
                  @endif
                </div>
                <div class="fg-card-subtitle">
                  {{ $unit->floor ? ucfirst($unit->floor) . ' Floor' : 'Ground Floor' }}
                  @if($unit->direction) · {{ strtoupper($unit->direction) }} @endif
                  @if($unit->outlook) · {{ $unit->outlook }} {{ str_contains(strtolower($unit->outlook), 'view') ? '' : 'View' }} @endif
                </div>
                <div class="fg-card-divider"></div>
                <div class="fg-card-price">
                  <span class="price">${{ number_format($unit->price, 0, ' ', ' ') }}</span>
                  @if($unit->internal_area && $unit->internal_area > 0)
                    <span class="sqft">${{ number_format($unit->price / $unit->internal_area, 0) }}/sqft</span>
                  @endif
                </div>
                @if($hasDiscount)
                  <button type="button" class="fg-discount" title="Limited time offer">Unlock ${{ number_format($unit->discount, 0, ',', ',') }} Discount</button>
                @endif
              </div>

              <!-- Stats row (6 boxes) -->
              <div class="fg-stats">
                <div class="fg-stat" title="Bedrooms">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 14v4h20v-4a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3z"/><path d="M2 14V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v7"/><path d="M7 11V9h10v2"/></svg>
                  <span class="v">{{ $unit->bedrooms ?? 0 }}</span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="Bathrooms">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6V4a2 2 0 0 1 4 0"/><path d="M2 11h20"/><path d="M5 11v6a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-6"/><line x1="6" y1="22" x2="6" y2="20"/><line x1="18" y1="22" x2="18" y2="20"/></svg>
                  <span class="v">{{ $unit->bathrooms ?? 0 }}</span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="Parking">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14"/><path d="M5 17V9l1.5-4h11L19 9v8"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                  <span class="v">{{ $unit->parking_bays ?? 0 }}</span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="Internal area">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" stroke-dasharray="2 2"/></svg>
                  <span class="v">{{ number_format(($unit->internal_area ?? 0)) }}m<sup>2</sup></span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="External area">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 8 3 3 8 3"/><polyline points="16 3 21 3 21 8"/><polyline points="21 16 21 21 16 21"/><polyline points="8 21 3 21 3 16"/></svg>
                  <span class="v">{{ number_format(($unit->external_area ?? 0)) }}m<sup>2</sup></span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="Total area">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V3h18"/><line x1="3" y1="9" x2="9" y2="9"/><line x1="3" y1="15" x2="9" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="9"/></svg>
                  <span class="v">{{ number_format(($unit->total_area ?? 0)) }}m<sup>2</sup></span>
                </div>
              </div>

              <!-- Buttons + availability -->
              <div class="fg-card-actions">
                @if($isSold)
                  <div class="fg-card-buttons">
                    <button class="fg-btn-info-similar" type="button" onclick="if(typeof openMoreInfo==='function'){openMoreInfo('{{ $unitId }}')}">View Similar Units</button>
                  </div>
                  <div class="fg-card-availability">
                    <span class="dot"></span>
                    <span>This unit has been sold.</span>
                  </div>
                @elseif($isReserved)
                  <div class="fg-card-buttons">
                    <button class="fg-btn-info" onclick="openMoreInfo('{{ $unitId }}')">More Info</button>
                    <button class="fg-btn-cta" type="button" disabled style="cursor:not-allowed;opacity:.5;">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h14l-1 12H6z"/><path d="M9 8V5a3 3 0 1 1 6 0v3"/></svg>
                      Reserved
                    </button>
                  </div>
                  <div class="fg-card-availability">
                    <span class="dot"></span>
                    <span>Currently on hold by another buyer.</span>
                  </div>
                @else
                  <div class="fg-card-buttons">
                    <button class="fg-btn-info" onclick="openMoreInfo('{{ $unitId }}')">More Info</button>
                    <button class="fg-btn-cta" onclick="openReservePage('{{ $unitId }}')">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="23 7 16 12 23 17 23 7" fill="currentColor"></polygon>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                      </svg>
                      Book Video Call
                    </button>
                  </div>
                  <div class="fg-card-availability advisor-live">
                    <span class="dot"></span>
                    <span>An advisor is available right now.</span>
                  </div>
                @endif
              </div>
            </div>
          </div>

          @if($isHighDem)
            <div class="fg-card-status-strip">
              <span class="fg-card-status-dot"></span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <span>{{ (int)($unit->views_today ?? 0) ?: $shortlistedCount }} people viewed this unit today</span>
            </div>
          @elseif($isPending)
            <div class="fg-card-status-strip">
              <span class="fg-card-status-dot"></span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>Pending review · Hold expires soon</span>
            </div>
          @elseif($isSecond)
            <div class="fg-card-status-strip">
              <span class="fg-card-status-dot"></span>
              @php
                $releasedDays = $unit->released_at ? \Carbon\Carbon::parse($unit->released_at)->diffInDays(now()) : null;
              @endphp
              <span>This unit was released {{ $releasedDays !== null ? ($releasedDays === 0 ? 'today' : $releasedDays.' '.\Illuminate\Support\Str::plural('day', $releasedDays).' ago') : 'recently' }}</span>
            </div>
          @elseif($isReserved)
            @php
              $reservedFuture = !empty($unit->reserved_until) && \Carbon\Carbon::parse($unit->reserved_until)->isFuture();
            @endphp
            <div class="fg-card-status-strip is-reserved-strip" @if($reservedFuture) data-reserved-until="{{ \Carbon\Carbon::parse($unit->reserved_until)->toIso8601String() }}" @endif>
              <span class="fg-card-status-dot"></span>
              @if($reservedFuture)
                <span>Reserved for <span class="fg-countdown" data-countdown>00:00:00</span> remaining</span>
              @else
                <span>Reserved · Awaiting deposit</span>
              @endif
            </div>
          @endif
        </div>
        <!---- unit - end   ---->

        <!-- Insert CTA card after 2 complete rows (6 units) -->
        @if($loop->iteration === 6)
        <div style="grid-column:1/-1;justify-self:center;width:100%;">
          <div style="position:relative;border-radius:28px;display:grid;grid-template-columns:1fr 1fr;overflow:hidden;background:#fff;box-shadow:0 1px 2px rgba(10,13,20,0.05);">
            <button style="position:absolute;top:12px;right:12px;width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,0.9);border:none;font-size:18px;cursor:pointer;z-index:5;display:flex;align-items:center;justify-content:center;color:#5c5c5c;">×</button>
            <div style="order:2;background:var(--brand);display:flex;flex-direction:column;justify-content:center;padding:32px 40px;font-family:'Inter',sans-serif;">
              <h4 style="margin:0 0 12px 0;font-weight:700;font-size:20px;color:white;line-height:28px;">Own Fully Furnished. Earn Effortlessly.</h4>
              <p style="margin:0 0 20px 0;font-size:14px;line-height:1.6;color:white;opacity:0.95;">Step into effortless ownership with a fully furnished unit—on us. Enjoy a free USD $30,000 furniture pack on launch and explore how Dolce Hotels &amp; Resorts by Wyndham professionally manages your investment for optimal returns.</p>
              <button style="background:#b4874a;color:white;border:none;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:500;cursor:pointer;width:fit-content;font-family:'Inter',sans-serif;">Download ROI's</button>
            </div>
            <div style="order:1;overflow:hidden;min-height:280px;">
              <img src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2FctaCards%2FxAm2it27WacYwCuJuage%2FFurniture%2F1773908563262%2Ffull.webp" alt="Own Fully Furnished. Earn Effortlessly." style="width:100%;height:100%;object-fit:cover;display:block;">
            </div>
          </div>
        </div>
        @endif

        @endforeach
      </div>

      <!-- LIST VIEW -->
      <div class="fg-list-wrap" id="fgListWrap">
        <div class="fg-list-toolbar">
          <label class="fg-list-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a3a3a3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" placeholder="Search unit, floor, type…" oninput="filterListRows(this.value)">
          </label>
          <div class="fg-list-tabs" role="tablist" aria-label="Status filter">
            <button type="button" class="fg-list-tab active" data-tab="all" onclick="setListTab(this)">All <span class="badge">{{ $units->count() }}</span></button>
            <button type="button" class="fg-list-tab" data-tab="available" onclick="setListTab(this)">Available <span class="badge">{{ $units->whereIn('status', ['available', null, ''])->count() ?: $units->where('status', '!=', 'sold')->count() }}</span></button>
            <button type="button" class="fg-list-tab" data-tab="pending" onclick="setListTab(this)">Pending <span class="badge">{{ $units->where('status', 'PENDING')->count() }}</span></button>
            <button type="button" class="fg-list-tab" data-tab="second" onclick="setListTab(this)">2nd Chance <span class="badge">{{ $units->filter(fn($u)=>!empty($u->is_second_chance))->count() }}</span></button>
            <button type="button" class="fg-list-tab" data-tab="sold" onclick="setListTab(this)">Sold <span class="badge">{{ $units->where('status', 'SOLD')->count() }}</span></button>
          </div>
          <button class="fg-pill-matches" type="button" style="margin-left:auto;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
            {{ $units->count() }} Matches
          </button>
        </div>
        <table class="fg-list-table" id="fgListTable">
          <thead>
            <tr>
              <th>Unit</th>
              <th>Status</th>
              <th>Floor</th>
              <th>Type</th>
              <th>Direction</th>
              <th>Bed/Bath</th>
              <th>Int sqft</th>
              <th>Ext sqft</th>
              <th>Price</th>
              <th>ROI</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($units as $unit)
              @php
                $st = strtolower($unit->status ?? '');
                $rowCls = 'row-available'; $statusCls = 'available'; $statusLabel = 'Available';
                if ($st === 'sold')                       { $rowCls = 'row-sold is-sold'; $statusCls = 'sold'; $statusLabel = 'Sold'; }
                elseif ($st === 'reserved')               { $rowCls = 'row-reserved'; $statusCls = 'reserved'; $statusLabel = 'Reserved'; }
                elseif ($st === 'pending')                { $rowCls = 'row-pending'; $statusCls = 'pending'; $statusLabel = 'Pending'; }
                elseif (!empty($unit->is_second_chance))  { $rowCls = 'row-second'; $statusCls = 'second'; $statusLabel = '2nd Chance'; }
                elseif (!empty($unit->is_high_demand))    { $rowCls = 'row-hot'; $statusCls = 'hot'; $statusLabel = 'Hot'; }
                $unitId = $unit->custom_id ?? $unit->id;
                $tabKey = $statusCls === 'hot' || $statusCls === 'available' || $statusCls === 'reserved' ? 'available' : $statusCls;
              @endphp
              <tr class="{{ $rowCls }}" data-tab="{{ $tabKey }}" data-search="{{ strtolower(($unitId ?? '') . ' ' . ($unit->floor ?? '') . ' ' . ($unit->bedrooms ?? '') . ' bed ' . ($unit->direction ?? '') . ' ' . ($unit->outlook ?? '')) }}">
                <td><b>{{ $unitId }}</b></td>
                <td>
                    <span class="fg-list-status {{ $statusCls }}">{{ strtoupper($statusLabel) }}</span>
                    @if($statusCls === 'reserved' && !empty($unit->reserved_until) && \Carbon\Carbon::parse($unit->reserved_until)->isFuture())
                        <div style="font-size:10px;color:#92400e;margin-top:2px;">Expira {{ \Carbon\Carbon::parse($unit->reserved_until)->diffForHumans() }}</div>
                    @endif
                </td>
                <td>{{ $unit->floor ? ucfirst($unit->floor) : 'Ground' }}</td>
                <td>{{ ($unit->bedrooms ?? 0) }} Bed{{ ($unit->bedrooms ?? 0) > 1 ? '' : '' }}</td>
                <td>{{ strtoupper($unit->direction ?? '—') }}</td>
                <td>{{ ($unit->bedrooms ?? 0) }} / {{ ($unit->bathrooms ?? 0) }}</td>
                <td>{{ number_format($unit->internal_area ?? 0) }}<sub style="font-size:9px;color:#a3a3a3;">sf</sub></td>
                <td>{{ number_format($unit->external_area ?? 0) }}<sub style="font-size:9px;color:#a3a3a3;">sf</sub></td>
                <td>
                  <span class="price">${{ number_format($unit->price, 0, ',', ',') }}</span>
                  @if($unit->internal_area && $unit->internal_area > 0)
                    <span class="price-meta">${{ number_format($unit->price / $unit->internal_area, 0) }}/SQFT</span>
                  @endif
                </td>
                <td>
                  @if(!empty($unit->roi))
                    <span class="roi">{{ $unit->roi }}%</span>
                  @else
                    <span class="roi" style="color:#a3a3a3;">—</span>
                  @endif
                </td>
                <td>
                  <div class="fg-list-actions">
                    <button class="fg-list-icon-btn" type="button" aria-label="Save">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                      </svg>
                    </button>
                    <button class="fg-list-info-btn" type="button" onclick="openMoreInfo('{{ $unitId }}')">INFO</button>
                    @if($statusCls === 'sold')
                      <button class="fg-list-cta" type="button" disabled>Sold</button>
                    @elseif($statusCls === 'reserved')
                      <button class="fg-list-cta" type="button" disabled style="opacity:.5;cursor:not-allowed;">Reserved</button>
                    @else
                      <button class="fg-list-cta" type="button" onclick="openReservePage('{{ $unitId }}')">Book Video Call</button>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div class="fg-list-pagination">
          <span>Page 1 of 1</span>
          <div class="fg-list-pages">
            <button class="fg-list-page" type="button" aria-label="Previous">‹</button>
            <button class="fg-list-page active" type="button">1</button>
            <button class="fg-list-page" type="button" aria-label="Next">›</button>
          </div>
        </div>
      </div>

      <!-- PLAN VIEW (Figma 193:9116 — Property 1=planta, makai=true) -->
      <div class="fg-plan-wrap" id="fgPlanWrap">
        <div class="fg-plan-board">

          <!-- chips-filters bar (Figma 193:6017) -->
          <div class="fg-plan-topbar">
            <div class="fg-plan-chips" role="tablist" aria-label="Floor filter">
              @php
                $floorChips = [
                  ['label' => 'Ground', 'count' => 12, 'active' => false],
                  ['label' => 'P1',     'count' => 10, 'active' => true],
                  ['label' => 'P2',     'count' => 8,  'active' => false],
                  ['label' => 'P3',     'count' => 7,  'active' => false],
                  ['label' => 'P4',     'count' => 5,  'active' => false],
                  ['label' => 'P5',     'count' => 3,  'active' => false],
                  ['label' => 'P6',     'count' => 9,  'active' => false],
                  ['label' => 'P7',     'count' => 13, 'active' => false],
                ];
              @endphp
              @foreach($floorChips as $c)
                <button type="button"
                        class="fg-chip-floor{{ $c['active'] ? ' is-active' : '' }}"
                        role="tab"
                        aria-selected="{{ $c['active'] ? 'true' : 'false' }}"
                        data-floor="{{ strtolower($c['label']) }}">
                  <span class="fg-chip-left">
                    <span class="fg-chip-dot"></span>
                    <span class="fg-chip-text">{{ $c['label'] }}</span>
                  </span>
                  <span class="fg-chip-count">{{ $c['count'] }}</span>
                </button>
              @endforeach
            </div>

            <!-- PISO 1 · 6 UNIDADES DISPONIBLES (Figma 193:6028) -->
            <div class="fg-plan-piso">
              <div class="fg-plan-piso-left">PISO 1</div>
              <div class="fg-plan-piso-right">6 UNIDADES DISPONIBLES</div>
            </div>
          </div>

          <!-- Map container (Figma 193:6600 — ContainerMap, 1366×769) -->
          <div class="fg-plan-canvas" style="background-color: white!important;" id="fgPlanCanvas">
            <!-- Planview image — labels, compass, and PHASE 1 are baked in -->
            <img src="/images/plan-view/makai-planview.png"
                 alt="Plan view — Ground Floor"
                 class="fg-plan-img"
                 draggable="false">

            <!-- Hotspot markers positioned in coords from Figma frame (1366×769) -->
            @php
              // Exact coordinates from Figma. Each marker is 72×72px.
              // Side = which corner the tail points to (Figma's `left` / `right` props).
              $planMarkers = [
                ['x'=>1184,  'y'=>295,  'state'=>'default',  'side'=>'left',  'unit'=>'A-101'],
                ['x'=>289,   'y'=>349,  'state'=>'default',  'side'=>'right', 'unit'=>'A-104'],
                ['x'=>1129,  'y'=>394,  'state'=>'hot',      'side'=>'left',  'unit'=>'C-103'],
                ['x'=>375,   'y'=>412,  'state'=>'sold',     'side'=>'right', 'unit'=>'B-110'],
                ['x'=>1082.5,'y'=>474.5,'state'=>'default',  'side'=>'left',  'unit'=>'C-108'],
              ];
              $markerFill = [
                'default'  => ['#5c7c68', '#455d4d'],
                'hot'      => ['#f06a23', '#c84e16'],
                'reserved' => ['#cd9600', '#a07700'],
                'sold'     => ['#9aa3a0', '#7e8784'],
                '2nd'      => ['#3b82f6', '#1d4ed8'],
              ];
            @endphp

            @foreach($planMarkers as $i => $m)
              @php
                // Position as % of 1366×769 canvas
                $leftPct = ($m['x'] / 1366) * 100;
                $topPct  = ($m['y'] / 769) * 100;
                // Resolve display data per marker — fall back to unit lookup if available
                $unitObj = isset($units) ? $units->first(fn($u) => ($u->custom_id ?? $u->id) == $m['unit']) : null;
                $markerPrice = $unitObj?->price ? '$'.number_format($unitObj->price/1000, 0).'k' : '$473k';
                $markerArea  = $unitObj?->internal_area ?? 120;
              @endphp
              <button type="button"
                      class="fg-plan-marker is-{{ $m['state'] }} side-{{ $m['side'] }}"
                      style="left:{{ number_format($leftPct, 4, '.', '') }}%;top:{{ number_format($topPct, 4, '.', '') }}%;"
                      onclick="openMoreInfo('{{ $m['unit'] }}')"
                      aria-label="Unit {{ $m['unit'] }}">
                <span class="fg-plan-marker-bubble">
                  @include('partials._plan_marker_svg', [
                      'state' => $m['state'],
                      'side'  => $m['side'],
                      'uid'   => $i.'_'.$m['state'],
                  ])
                  <span class="fg-plan-marker-text">
                    <span class="fg-plan-marker-price">{{ $markerPrice }}</span>
                    <span class="fg-plan-marker-sqft">{{ $markerArea }}</span>
                  </span>
                  @if($m['state'] === 'hot')
                    <span class="fg-plan-marker-fire" aria-hidden="true">
                      <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 23a7 7 0 0 1-7-7c0-2 1-3 1-3 0 1 1 2 2 2 0-3 2-5 2-8 0-2-1-3-1-3 4 0 8 4 8 9 1-1 2-2 2-4 2 1 3 4 3 7a7 7 0 0 1-7 7z"/></svg>
                    </span>
                  @endif
                </span>
              </button>
            @endforeach
          </div>

        </div>
      </div>

    </div>

    <!-- Footer -->
    <footer class="fg-footer" data-node-id="124:3620">
      <div class="fg-footer-content" data-node-id="124:3621">
        <!-- Logo -->
        <div class="fg-footer-logo" data-node-id="124:3656">
          <img src="/images/makai-logo.png" alt="logo" class="logo-img" style="max-height:30px;max-width:160px;object-fit:contain;">
        </div>
        
        <!-- Copyright -->
        <div class="fg-footer-copyright" data-node-id="124:3626">
          <p>©2026 Duna Development — Todos los derechos reservados</p>
        </div>
        
        <!-- Social Icons -->
        <div class="fg-footer-social" data-node-id="124:3627">
          <!-- Facebook -->
          <div class="fg-social-icon" data-node-id="124:3628">
            <div class="fg-icon-facebook" data-node-id="124:3629">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </div>
          </div>
          
          <!-- Instagram -->
          <div class="fg-social-icon" data-node-id="124:3630">
            <div class="fg-icon-instagram" data-node-id="124:3631">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
              </svg>
            </div>
          </div>
          
          <!-- Twitter/X -->
          <div class="fg-social-icon" data-node-id="124:3632">
            <div class="fg-icon-twitter" data-node-id="124:3633">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-3.46 13.523l.916-1.115L6.644 4.236H4.853l7.234 9.06 2.632 3.317z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <div style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:999;">
      <button style="width:3.5rem;height:3.5rem;border-radius:50%;background:#25D366;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="white">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
        </svg>
      </button>
    </div>

  </div><!-- end mainPage -->


  <script>
    // ============================
    // MORE INFO MODAL
    // ============================
    let modalImages = [
      'https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FA_16_LA_MA_AXO_T1A_HR%2F1773673791087%2Ffull.webp',
      'https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FB_Makai_Cards_Unit_Layout_111-T1A%2F1773673791087%2Ffull.webp',
      'https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FC_Makai_Floorplans_First_Floor_111%2F1773673791087%2Ffull.webp',
      'https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FD_Makai_Floorplans_Second_Floor_111%2F1773673791087%2Ffull.webp'
    ];
    let currentModalImg = 0;


    // Helper function for number formatting (similar to PHP's number_format)
    function number_format(number, decimals = 0, dec_point = '.', thousands_sep = ' ') {
      const n = !isFinite(+number) ? 0 : +number;
      const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
      const sep = typeof thousands_sep === 'undefined' ? ',' : thousands_sep;
      const dec = typeof dec_point === 'undefined' ? '.' : dec_point;
      
      const s = (prec ? n.toFixed(prec) : Math.round(n)).toString().split('.');
      if (sep) {
        let re = /(-?\d+)(\d{3})/;
        while (re.test(s[0])) {
          s[0] = s[0].replace(re, '$1' + sep + '$2');
        }
      }
      if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
      }
      return s.join(dec);
    }

    function openMoreInfo(unitId) {
      console.log('Opening unit info for ID:', unitId);
      
      // Update currentOpenUnit
      currentOpenUnit = unitId;
      
      // Fetch unit data from API
      fetch(`/api/units/${unitId}`)
        .then(response => {
          console.log('API response status:', response.status);
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(unit => {
          console.log('Unit data received:', unit);
          
          // Update modal content with real data
          const unitNum = document.getElementById('modalUnitNum');
          const unitPrice = document.getElementById('modalPrice');
          const unitDesc = document.getElementById('modalDesc');
          
          if (unitNum) unitNum.textContent = unit.custom_id || unit.name || 'Unit ' + unit.id;
          if (unitPrice) unitPrice.textContent = unit.price ? `$${number_format(unit.price, 0, ' ', ' ')}` : 'Price not available';

          // Stat boxes — populate from DB; show — when missing
          const setStat = (id, value, suffix='') => {
              const el = document.getElementById(id);
              if (!el) return;
              if (value === null || value === undefined || value === '' || value === 0) {
                  el.textContent = (id === 'modalStatPool' || id === 'modalStatPark') ? '0' : '—';
              } else {
                  el.textContent = (typeof value === 'number' ? number_format(value, 0, ',', ',') : value) + suffix;
              }
          };
          setStat('modalStatBed',   unit.bedrooms);
          setStat('modalStatBath',  unit.bathrooms);
          setStat('modalStatPark',  unit.parking_bays);
          setStat('modalStatPool',  unit.pools);
          setStat('modalStatInt',   unit.internal_area);
          setStat('modalStatExt',   unit.external_area);
          setStat('modalStatTotal', unit.total_area);

          // Shortlisted count
          const sl = document.getElementById('modalShortlistedCount');
          if (sl) sl.textContent = unit.shortlisted_count || 0;

          // Financial rows — hide when no DB value
          const fmtMoney = v => '$' + number_format(v, 0, ',', ',');
          const toggleRow = (rowId, valId, value) => {
              const row = document.getElementById(rowId);
              const val = document.getElementById(valId);
              if (!row || !val) return;
              if (value && Number(value) > 0) {
                  val.textContent = fmtMoney(value);
                  row.style.display = '';
              } else {
                  row.style.display = 'none';
              }
          };
          // Investment view
          toggleRow('modalRowLevies', 'modalLevies', unit.levies);
          toggleRow('modalRowRental', 'modalRental', unit.est_rental);
          const feesSum = [unit.expense_1, unit.expense_2, unit.expense_3].reduce((a, b) => Number(a||0) + Number(b||0), 0);
          toggleRow('modalRowFees',   'modalFees',  feesSum);
          toggleRow('modalRowRates',  'modalRates', unit.rates);

          // Living view (no rental, plus total monthly cost)
          toggleRow('modalRowLeviesL', 'modalLeviesL', unit.levies);
          toggleRow('modalRowFeesL',   'modalFeesL',   feesSum);
          toggleRow('modalRowRatesL',  'modalRatesL',  unit.rates);
          const totalCost = Number(unit.levies || 0) + Number(unit.rates || 0) + Number(feesSum || 0);
          toggleRow('modalRowTotalCost', 'modalTotalCost', totalCost);

          // Living extras
          const setTextRow = (rowId, valId, value) => {
              const row = document.getElementById(rowId);
              const val = document.getElementById(valId);
              if (!row || !val) return;
              if (value !== null && value !== undefined && value !== '') {
                  val.textContent = value;
                  row.style.display = '';
              } else { row.style.display = 'none'; }
          };
          // Amenities with icons
          const amenRow = document.getElementById('modalRowAmen');
          const amenVal = document.getElementById('modalAmenities');
          if (amenRow && amenVal) {
              const amenities = unit.amenities || [];
              if (Array.isArray(amenities) && amenities.length > 0) {
                  const amenityIcons = {
                      'pool': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20"/><path d="M4 12v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6"/><path d="M6 12V8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v4"/></svg>',
                      'gym': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5h11"/><path d="M6 20v-8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8"/><path d="M18 11V6a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v5"/></svg>',
                      'beach_club': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22h20"/><path d="M12 2v20"/><path d="M4 12c0-4 3-7 8-7s8 3 8 7"/></svg>',
                      'restaurant': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>',
                      'spa': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c-5.5 0-10 4.5-10 10s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 2v20"/><path d="M2 12h20"/></svg>',
                      'tennis': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2v20"/><path d="M4.93 4.93l14.14 14.14"/><path d="M19.07 4.93L4.93 19.07"/></svg>',
                      'golf': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="3"/><path d="M12 13v8"/><path d="M9 6l3-4 3 4"/></svg>',
                      'security': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                      'parking': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M7 15V9"/><path d="M17 15V9"/></svg>',
                      'concierge': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                      'playground': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
                      'bbq': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16"/><path d="M6 12v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-4"/><path d="M8 12V8a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"/></svg>',
                  };
                  const amenityLabels = {
                      'pool': 'Pool',
                      'gym': 'Gym',
                      'beach_club': 'Beach Club',
                      'restaurant': 'Restaurant',
                      'spa': 'Spa',
                      'tennis': 'Tennis Court',
                      'golf': 'Golf Course',
                      'security': '24/7 Security',
                      'parking': 'Parking',
                      'concierge': 'Concierge',
                      'playground': 'Playground',
                      'bbq': 'BBQ Area',
                  };
                  let html = '<div style="display:flex;flex-wrap:wrap;gap:8px;">';
                  amenities.forEach(key => {
                      if (amenityIcons[key]) {
                          html += '<div style="display:flex;align-items:center;gap:4px;font-size:12px;color:var(--ink-700);">' + amenityIcons[key] + '<span>' + amenityLabels[key] + '</span></div>';
                      }
                  });
                  html += '</div>';
                  amenVal.innerHTML = html;
                  amenRow.style.display = '';
              } else {
                  amenRow.style.display = 'none';
              }
          }
          setTextRow('modalRowWalk',   'modalWalkScore', unit.walk_score);
          setTextRow('modalRowSchool', 'modalSchool',    unit.school_proximity);

          // Investment longform + Living longform
          const showText = (id, text) => {
              const el = document.getElementById(id);
              if (!el) return;
              if (text && String(text).trim() !== '') { el.textContent = text; el.style.display = ''; }
              else { el.style.display = 'none'; }
          };
          showText('modalInvestmentText', unit.for_investment_text);
          showText('modalLivingText',     unit.for_living_text);

          // Projected value + ROI (investment)
          const proj      = Number(unit.projected_value || 0);
          const projYear  = unit.projected_value_year || '';
          const roi       = unit.roi_percent ? Number(unit.roi_percent) : null;
          const projBox   = document.getElementById('modalProjected');
          if (projBox) {
              if (proj > 0) {
                  document.getElementById('modalProjectedNow').textContent    = '$' + number_format(unit.price || 0, 0, ',', ',') + ' today';
                  document.getElementById('modalProjectedFuture').textContent = '$' + number_format(proj, 0, ',', ',') + '+';
                  document.getElementById('modalProjectedHint').textContent   = projYear ? ('est. ' + projYear) : (roi !== null ? roi + '% ROI' : '');
                  projBox.style.display = '';
              } else { projBox.style.display = 'none'; }
          }

          // Comparison text (investment)
          const cmpBox = document.getElementById('modalCompare');
          if (cmpBox) {
              if (unit.comparison_text && String(unit.comparison_text).trim() !== '') {
                  document.getElementById('modalCompareText').textContent = unit.comparison_text;
                  cmpBox.style.display = '';
              } else { cmpBox.style.display = 'none'; }
          }

          // Reflect availability — disable Reserve Online if the unit is on hold or sold
          const statusRaw = (unit.status || 'AVAILABLE').toString().toLowerCase();
          const isReserved = statusRaw === 'reserved';
          const isSold     = statusRaw === 'sold';
          const isPending  = statusRaw === 'pending';

          const badgeEl = document.getElementById('modalStatusBadge');
          const textEl  = document.getElementById('modalStatusText');
          const btn     = document.getElementById('modalReserveBtn');

          if (textEl) {
              textEl.textContent = isSold ? 'SOLD'
                                : isReserved ? 'RESERVED'
                                : isPending ? 'PENDING'
                                : 'AVAILABLE';
          }
          if (badgeEl) {
              // override pill colors when not available
              badgeEl.style.background = isSold || isReserved ? '#fde2e1'
                                       : isPending ? '#fef3c7'
                                       : '';
              badgeEl.style.color      = isSold || isReserved ? '#b91c1c'
                                       : isPending ? '#92400e'
                                       : '';
          }
          if (btn) {
              const blocked = isSold || isReserved || isPending;
              btn.disabled = blocked;
              btn.style.opacity = blocked ? '0.45' : '';
              btn.style.cursor  = blocked ? 'not-allowed' : '';
              if (isReserved) {
                  const until = unit.reserved_until ? new Date(unit.reserved_until) : null;
                  btn.textContent = until && until > new Date()
                      ? 'Reserved until ' + until.toLocaleDateString()
                      : 'Reserved';
              } else if (isSold) {
                  btn.textContent = 'Sold';
              } else if (isPending) {
                  btn.textContent = 'Pending';
              } else {
                  btn.textContent = 'Reserve Online';
              }
          }
          
          // Build description
          let description = '';
          if (unit.floor) description += unit.floor.charAt(0).toUpperCase() + unit.floor.slice(1);
          if (unit.bedrooms) description += ` | ${unit.bedrooms} Bed`;
          if (unit.bathrooms) description += ` | ${unit.bathrooms} Bath`;
          if (unit.direction) description += ` | ${unit.direction.toUpperCase()}`;
          if (unit.outlook) description += ` | ${unit.outlook}`;
          if (unitDesc) unitDesc.textContent = description || 'Unit details';

          // Update images
          modalImages = unit.images && unit.images.length > 0 
            ? unit.images.map(img => img.path) 
            : ['https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2FctaCards%2FUIpwnmJz8oBQ2cKHMMA6%2Ftwo_bed%2F1773908343700%2Ffull.webp'];

          console.log('Modal images:', modalImages);

          // Reset image to first
          currentModalImg = 0;
          updateModalImage();

          // Re-apply current buyer mode to hide/show the right blocks
          if (typeof window.applyBuyerMode === 'function') {
            const activeBtn = document.querySelector('.mt-buyer-toggle button.active');
            window.applyBuyerMode(activeBtn?.dataset.buyer || 'investment');
          }

          // Show modal
          const modal = document.getElementById('moreInfoModal');
          if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            console.log('Modal should be visible now');
          } else {
            console.error('Modal element not found!');
          }

          // Track this view (fire-and-forget; dedupe handled server-side)
          try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            fetch(`/api/units/${unit.id}/view`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
              credentials: 'same-origin',
            }).catch(() => {});
          } catch(e) {}
        })
        .catch(error => {
          console.error('Error fetching unit data:', error);
          console.log('Using fallback data');
          
          // Fallback to default data if API fails
          const data = unitModalData['111'];
          document.getElementById('modalUnitNum').textContent = data.unit;
          document.getElementById('modalPrice').textContent = data.price;
          document.getElementById('modalDesc').textContent = data.floor + ' | ' + data.desc;
          
          modalImages = ['https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2FctaCards%2FUIpwnmJz8oBQ2cKHMMA6%2Ftwo_bed%2F1773908343700%2Ffull.webp'];
          currentModalImg = 0;
          updateModalImage();
          
          const modal = document.getElementById('moreInfoModal');
          if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
          }
        });
    }

    function closeMoreInfo() {
      document.getElementById('moreInfoModal').style.display = 'none';
      document.body.style.overflow = '';
    }

    function updateModalImage() {
      const img = document.getElementById('modalMainImg');
      if (img) {
        img.src = modalImages[currentModalImg];
        const counter = document.getElementById('modalImgCounter');
        if (counter) counter.textContent = (currentModalImg + 1) + ' / ' + modalImages.length;
      }
      // Sync thumbs (Figma modal-tipologia)
      const thumbsWrap = document.getElementById('mtThumbs');
      if (thumbsWrap) {
        const thumbs = thumbsWrap.querySelectorAll('.mt-thumb');
        thumbs.forEach((t, i) => {
          t.classList.toggle('active', i === currentModalImg);
          const tImg = t.querySelector('img');
          if (tImg && modalImages[i]) tImg.src = modalImages[i];
          t.style.display = modalImages[i] ? '' : 'none';
        });
      }
    }

    function prevModalImg() {
      currentModalImg = (currentModalImg - 1 + modalImages.length) % modalImages.length;
      updateModalImage();
    }

    function nextModalImg() {
      currentModalImg = (currentModalImg + 1) % modalImages.length;
      updateModalImage();
    }

    // Modal Tipologia — thumb clicks + toggles
    document.addEventListener('DOMContentLoaded', function () {
      const thumbsWrap = document.getElementById('mtThumbs');
      if (thumbsWrap) {
        thumbsWrap.addEventListener('click', function (e) {
          const btn = e.target.closest('.mt-thumb');
          if (!btn) return;
          const idx = parseInt(btn.dataset.idx, 10);
          if (Number.isFinite(idx) && modalImages[idx]) {
            currentModalImg = idx;
            updateModalImage();
          }
        });
      }
      // Currency toggle
      document.querySelectorAll('.mt-currency-toggle button').forEach(function (b) {
        b.addEventListener('click', function () {
          this.parentElement.querySelectorAll('button').forEach(x => x.classList.remove('active'));
          this.classList.add('active');
        });
      });
      // Buyer toggle — toggles a `living-mode` class on .mt-shell; CSS does the rest
      function applyBuyerMode(mode) {
        const shell = document.querySelector('#moreInfoModal .mt-shell');
        if (!shell) return;
        shell.classList.toggle('living-mode', mode === 'living');
      }
      window.applyBuyerMode = applyBuyerMode;
      const activeBuyerBtn = document.querySelector('.mt-buyer-toggle button.active');
      if (activeBuyerBtn) applyBuyerMode(activeBuyerBtn.dataset.buyer || 'investment');

      document.querySelectorAll('.mt-buyer-toggle button').forEach(function (b) {
        b.addEventListener('click', function () {
          this.parentElement.querySelectorAll('button').forEach(x => x.classList.remove('active'));
          this.classList.add('active');
          applyBuyerMode(this.dataset.buyer || 'investment');
        });
      });
      // No / With dimensions toggle
      document.querySelectorAll('.mt-dim-toggle button').forEach(function (b) {
        b.addEventListener('click', function () {
          this.parentElement.querySelectorAll('button').forEach(x => x.classList.remove('active'));
          this.classList.add('active');
        });
      });
      // Plan floor chips
      document.querySelectorAll('.fg-chip-floor').forEach(function (b) {
        b.addEventListener('click', function () {
          this.parentElement.querySelectorAll('.fg-chip-floor').forEach(x => {
            x.classList.remove('is-active');
            x.setAttribute('aria-selected', 'false');
          });
          this.classList.add('is-active');
          this.setAttribute('aria-selected', 'true');
        });
      });
    });

    // ============================
    // RESERVE PAGE
    // ============================
    let reserveTimerInterval = null;
    let reserveTimeLeft = 600; // 10 minutes

    function openReservePage(unitId = null) {
      closeMoreInfo();

      const finalUnitId = unitId || currentOpenUnit || '1';
      currentOpenUnit = finalUnitId;

      // Si el usuario no está autenticado, redirigir al login con intent de reservar la unidad
      @guest
        window.location.href = '/login?intent=reserve&unit=' + encodeURIComponent(finalUnitId);
        return;
      @endguest

      // Usuario autenticado → crear la reserva directamente con datos del account y saltar al /form
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      fetch('/reservations', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ unit_id: finalUnitId, _token: csrfToken })
      })
      .then(r => r.json().then(d => ({ ok: r.ok, body: d })))
      .then(({ ok, body }) => {
        if (ok && body.success) {
          window.location.href = body.redirect_to || '/form';
        } else {
          alert(body.message || 'No se pudo crear la reserva. Intenta de nuevo.');
        }
      })
      .catch(err => {
        console.error('Error creando reserva:', err);
        alert('Error de red. Intenta de nuevo.');
      });
    }

    function closeReservePage() {
      document.getElementById('reservePage').style.display = 'none';
      document.body.style.overflow = '';
      clearInterval(reserveTimerInterval);
      reserveTimeLeft = 600;
    }

    function startReserveTimer() {
      clearInterval(reserveTimerInterval);
      reserveTimerInterval = setInterval(() => {
        reserveTimeLeft--;
        if (reserveTimeLeft <= 0) {
          clearInterval(reserveTimerInterval);
          reserveTimeLeft = 0;
        }
        updateTimerDisplays();
      }, 1000);
      updateTimerDisplays();
    }

    function updateTimerDisplays() {
      const mins = Math.floor(reserveTimeLeft / 60).toString().padStart(2, '0');
      const secs = (reserveTimeLeft % 60).toString().padStart(2, '0');
      const timeStr = mins + ':' + secs;
      ['reserveTimer1', 'reserveTimer2', 'reserveTimer3'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = timeStr;
      });
      // Update circle timer
      const path = document.getElementById('timerPath');
      if (path) {
        const fraction = reserveTimeLeft / 600;
        const dashArray = (fraction * 283).toFixed(0) + ' 283';
        path.setAttribute('stroke-dasharray', dashArray);
        // Color changes
        const color = reserveTimeLeft > 300 ? 'rgb(65,184,131)' : reserveTimeLeft > 120 ? 'orange' : 'red';
        path.style.stroke = color;
      }
      // Update min/sec displays
      const minEl = document.getElementById('timerMin3');
      const secEl = document.getElementById('timerSec3');
      if (minEl) minEl.textContent = mins;
      if (secEl) secEl.textContent = secs;
    }

    function goToReserveStep2(e) {
      if (e) e.preventDefault();
      
      const form = document.getElementById('reservationForm');
      const formData = new FormData(form);
      
      // Check if unit_id is set
      const unitId = formData.get('unit_id');
      if (!unitId) {
        alert('Unit ID is missing. Please try again.');
        console.error('Unit ID not found in form');
        return;
      }
      
      // Check if terms checkbox is checked
      const termsCheckbox = form.querySelector('input[type="checkbox"]');
      if (!termsCheckbox.checked) {
        alert('Please accept the Terms and Conditions to continue.');
        return;
      }
      
      // Show loading state
      const submitButton = form.querySelector('button[type="submit"]');
      const originalText = submitButton.textContent;
      submitButton.textContent = 'Processing...';
      submitButton.disabled = true;
      
      // Send data to API
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      const requestData = {
        first_name: formData.get('first_name'),
        last_name: formData.get('last_name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        country: formData.get('country'),
        unit_id: formData.get('unit_id'),
        _token: csrfToken
      };
      
      console.log('Sending reservation data:', requestData);
      console.log('CSRF Token:', csrfToken);
      
      fetch('/reservations', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
      })
      .then(response => {
        if (!response.ok) {
          if (response.status === 422) {
            return response.json().then(data => {
              throw new Error('Validation failed: ' + JSON.stringify(data.errors || data.message));
            });
          }
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Redirect to /form page
          window.location.href = data.redirect_to || '/form';
        } else {
          throw new Error(data.message || 'Error creating reservation');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('There was an error processing your reservation. Please try again.');
        
        // Reset button state
        submitButton.textContent = originalText;
        submitButton.disabled = false;
      });
    }

    function backToStep1() {
      document.getElementById('reserveStep1').style.display = 'block';
      document.getElementById('reserveStep2').style.display = 'none';
      document.getElementById('reserveStep3').style.display = 'none';
    }

    function goToReserveStep3() {
      document.getElementById('reserveStep1').style.display = 'none';
      document.getElementById('reserveStep2').style.display = 'none';
      document.getElementById('reserveStep3').style.display = 'block';
      document.getElementById('reservePage').scrollTo(0, 0);
    }

    // Active Users System
    let activeUsersInterval;
    let heartbeatInterval;
    let dynamicUsersInterval;
    let currentDynamicUsers = 1;

    // Function to calculate dynamic users based on timezone
    function calculateDynamicUsers() {
      const now = new Date();
      const hour = now.getHours();
      
      // Base users based on time of day (peak hours: 9-12, 14-18, 19-22)
      let baseUsers;
      if (hour >= 9 && hour < 12) {
        baseUsers = Math.floor(Math.random() * 5) + 8; // 8-12 users
      } else if (hour >= 14 && hour < 18) {
        baseUsers = Math.floor(Math.random() * 6) + 10; // 10-15 users
      } else if (hour >= 19 && hour < 22) {
        baseUsers = Math.floor(Math.random() * 4) + 6; // 6-9 users
      } else if (hour >= 6 && hour < 9) {
        baseUsers = Math.floor(Math.random() * 3) + 3; // 3-5 users
      } else if (hour >= 22 || hour < 6) {
        baseUsers = Math.floor(Math.random() * 2) + 1; // 1-2 users
      } else {
        baseUsers = Math.floor(Math.random() * 4) + 4; // 4-7 users
      }
      
      return baseUsers;
    }

    // Function to update dynamic users with fluctuation
    function updateDynamicUsers() {
      const change = Math.random() > 0.5 ? 1 : -1;
      const shouldChange = Math.random() > 0.7; // 30% chance to change
      
      if (shouldChange) {
        currentDynamicUsers = Math.max(1, currentDynamicUsers + change);
        
        // Occasionally reset to base calculation
        if (Math.random() > 0.95) {
          currentDynamicUsers = calculateDynamicUsers();
        }
      }
      
      const elements = document.querySelectorAll('[data-active-users]');
      elements.forEach(element => {
        element.textContent = currentDynamicUsers;
      });
    }

    // Function to update active users count
    function updateActiveUsersCount() {
      fetch('/api/active-users')
        .then(response => response.json())
        .then(data => {
          const elements = document.querySelectorAll('[data-active-users]');
          elements.forEach(element => {
            // If real count is low (less than 3), use dynamic fake count
            if (data.count < 3) {
              if (currentDynamicUsers === 1) {
                currentDynamicUsers = calculateDynamicUsers();
              }
              element.textContent = currentDynamicUsers;
            } else {
              element.textContent = data.count;
              currentDynamicUsers = data.count;
            }
          });
        })
        .catch(error => {
          console.error('Error fetching active users:', error);
          // Fallback to dynamic users on error
          if (currentDynamicUsers === 1) {
            currentDynamicUsers = calculateDynamicUsers();
          }
          const elements = document.querySelectorAll('[data-active-users]');
          elements.forEach(element => {
            element.textContent = currentDynamicUsers;
          });
        });
    }

    // Function to send heartbeat (update last seen)
    function sendHeartbeat() {
      fetch('/api/update-last-seen', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.count) {
            const elements = document.querySelectorAll('[data-active-users]');
            elements.forEach(element => {
              // If real count is low (less than 3), use dynamic fake count
              if (data.count < 3) {
                if (currentDynamicUsers === 1) {
                  currentDynamicUsers = calculateDynamicUsers();
                }
                element.textContent = currentDynamicUsers;
              } else {
                element.textContent = data.count;
                currentDynamicUsers = data.count;
              }
            });
          }
        })
        .catch(error => console.error('Error sending heartbeat:', error));
    }

    // Initialize active users tracking
    function initActiveUsersTracking() {
      // Initial count
      updateActiveUsersCount();

      // Send heartbeat every 2 minutes
      heartbeatInterval = setInterval(sendHeartbeat, 120000);

      // Update count every 30 seconds
      activeUsersInterval = setInterval(updateActiveUsersCount, 30000);

      // Update dynamic users every 5-10 seconds (fluctuation)
      dynamicUsersInterval = setInterval(updateDynamicUsers, 7000);

      // Send initial heartbeat
      sendHeartbeat();
    }

    // Start tracking when page loads
    document.addEventListener('DOMContentLoaded', initActiveUsersTracking);

    // Clean up intervals when page unloads
    window.addEventListener('beforeunload', () => {
      if (activeUsersInterval) clearInterval(activeUsersInterval);
      if (heartbeatInterval) clearInterval(heartbeatInterval);
      if (dynamicUsersInterval) clearInterval(dynamicUsersInterval);
    });

    // ============================
    // PROJECTS DROPDOWN
    // ============================
    function toggleProjects() {
      const dropdown = document.getElementById('projectsDropdown');
      if (!dropdown) return;
      const isOpen = dropdown.classList.contains('projects-open');
      if (isOpen) {
        closeProjects();
      } else {
        openProjects();
      }
    }

    function openProjects() {
      const dropdown = document.getElementById('projectsDropdown');
      const trigger = document.getElementById('projectsToggle');
      if (dropdown) dropdown.classList.add('projects-open');
      if (trigger) {
        trigger.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
      }
      // Close when clicking outside
      setTimeout(() => {
        document.addEventListener('click', closeProjectsOnOutsideClick);
        document.addEventListener('keydown', closeProjectsOnEscape);
      }, 10);
    }

    function closeProjects() {
      const dropdown = document.getElementById('projectsDropdown');
      const trigger = document.getElementById('projectsToggle');
      if (dropdown) dropdown.classList.remove('projects-open');
      if (trigger) {
        trigger.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
      }
      document.removeEventListener('click', closeProjectsOnOutsideClick);
      document.removeEventListener('keydown', closeProjectsOnEscape);
    }

    function closeProjectsOnOutsideClick(e) {
      const dropdown = document.getElementById('projectsDropdown');
      if (dropdown && !dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleProjects"]')) {
        closeProjects();
      }
    }

    function closeProjectsOnEscape(e) {
      if (e.key === 'Escape') closeProjects();
    }

    function selectProject(projectName) {
      const key = (projectName || '').toLowerCase();
      const cards = document.querySelectorAll('#projectsDropdown .project-card');
      cards.forEach(card => {
        if (card.getAttribute('data-project') === key) {
          card.classList.add('active');
        } else {
          card.classList.remove('active');
        }
      });
      switchHeroProject(key);
      closeProjects();
    }

    const PROJECT_BRAND_RGB = {
      makai:  [92, 124, 104],
      naviva: [197, 191, 86],
      liv:    [85, 127, 128],
    };

    function setBrandFor(project) {
      const rgb = PROJECT_BRAND_RGB[project] || PROJECT_BRAND_RGB.makai;
      const root = document.documentElement;
      const tuple = rgb.join(',');
      root.style.setProperty('--brand',         'rgb(' + tuple + ')');
      root.style.setProperty('--brand-soft',    'rgba(' + tuple + ',0.10)');
      root.style.setProperty('--brand-soft-2',  'rgba(' + tuple + ',0.08)');
      root.style.setProperty('--brand-soft-3',  'rgba(' + tuple + ',0.18)');
      root.style.setProperty('--brand-ring',    'rgba(' + tuple + ',0.45)');
      root.style.setProperty('--brand-overlay', 'rgba(' + tuple + ',0.78)');
      
      // Update logo images with smooth animation and spacing adjustment
      const logoPath = '/images/' + project + '-logo.png';
      const logoImages = document.querySelectorAll('.logo-img');
      const logoContainers = document.querySelectorAll('.logo-container');
      const buttonTriggers = document.querySelectorAll('.logo-trigger');
      
      // Trigger button animation
      buttonTriggers.forEach(button => {
        button.classList.add('switching');
        setTimeout(() => {
          button.classList.remove('switching');
        }, 800);
      });
      
      logoImages.forEach((img, index) => {
        // Fade out current logo
        img.classList.add('switching');
        
        // Change image and fade in after a short delay
        setTimeout(() => {
          img.src = logoPath;
          img.classList.remove('switching');
          
          // Adjust spacing after image loads
          img.onload = () => {
            const logoWidth = img.naturalWidth;
            const logoHeight = img.naturalHeight;
            const maxHeight = 32; // CSS height for logo
            const maxWidth = 160;
            
            // Calculate scaled dimensions maintaining aspect ratio
            const scaleFactor = Math.min(maxHeight / logoHeight, maxWidth / logoWidth, 1);
            const actualWidth = logoWidth * scaleFactor;
            const actualHeight = logoHeight * scaleFactor;
            
            // Adjust container width and spacing based on actual logo width
            if (logoContainers[index]) {
              // Set container to exact width of logo + padding
              const containerPadding = 8; // padding from link element
              const containerWidth = actualWidth + (containerPadding * 2);
              
              logoContainers[index].style.width = containerWidth + 'px';
              
              // Adjust gap between logo and button based on width
              const baseGap = 8;
              const widthFactor = actualWidth / 100; // normalize around 100px
              const adjustedGap = baseGap + (widthFactor * 2);
              
              logoContainers[index].parentElement.style.gap = Math.max(8, Math.min(16, adjustedGap)) + 'px';
            }
          };
        }, 200);
      });
    }

    function switchHeroProject(project) {
      const hero = document.getElementById('hero');
      if (!hero || !project) return;
      const current = hero.dataset.active;
      if (current === project) return;

      const prevText = hero.querySelector('.fg-hero-text[data-project="' + current + '"]');
      const prevBuilding = hero.querySelector('.fg-hero-building[data-project="' + current + '"]');

      // Trigger color swap immediately — CSS transition (0.9s) makes it gradual
      // so the palette settles right around when the new project finishes entering.
      setBrandFor(project);

      [prevText, prevBuilding].forEach(el => {
        if (!el) return;
        el.classList.remove('is-exiting');
        void el.offsetWidth; // force reflow to restart animation
        el.classList.add('is-exiting');
      });

      // After exit completes, swap active project (entrance triggers via CSS)
      setTimeout(() => {
        [prevText, prevBuilding].forEach(el => el && el.classList.remove('is-exiting'));
        hero.dataset.active = project;
      }, 700);
    }

    // Apply initial brand on load (matches the default data-active on the hero)
    document.addEventListener('DOMContentLoaded', () => {
      const hero = document.getElementById('hero');
      if (hero && hero.dataset.active) setBrandFor(hero.dataset.active);
    });

    // ============================
    // MENU DROPDOWN
    // ============================
    function toggleMenu() {
      // Toggle profile dropdown instead
      toggleProfileMenu();
      
      // Also toggle regular menu if needed
      const dropdown = document.getElementById('menuDropdown');
      if (!dropdown) return;
      const isOpen = dropdown.classList.contains('menu-open');
      if (isOpen) {
        closeMenu();
      } else {
        openMenu();
      }
    }

    function openMenu() {
      const dropdown = document.getElementById('menuDropdown');
      if (dropdown) dropdown.classList.add('menu-open');
      // Close when clicking outside
      setTimeout(() => {
        document.addEventListener('click', closeMenuOnOutsideClick);
      }, 10);
    }

    function closeMenu() {
      const dropdown = document.getElementById('menuDropdown');
      if (dropdown) dropdown.classList.remove('menu-open');
      document.removeEventListener('click', closeMenuOnOutsideClick);
    }

    function closeMenuOnOutsideClick(e) {
      const dropdown = document.getElementById('menuDropdown');
      if (dropdown && !dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleMenu"]')) {
        closeMenu();
      }
    }

    // ============================
    // PROFILE DROPDOWN
    // ============================
    function toggleProfileMenu() {
      const dropdown = document.getElementById('profileDropdown');
      if (!dropdown) return;
      const isOpen = dropdown.classList.contains('menu-open');
      
      // Close other dropdowns first
      closeMenu();
      closeProjects();
      
      if (isOpen) {
        dropdown.classList.remove('menu-open');
        document.removeEventListener('click', closeProfileMenuOnOutsideClick);
      } else {
        dropdown.classList.add('menu-open');
        setTimeout(() => {
          document.addEventListener('click', closeProfileMenuOnOutsideClick);
        }, 10);
      }
    }

    function closeProfileMenu() {
      const dropdown = document.getElementById('profileDropdown');
      if (dropdown) dropdown.classList.remove('menu-open');
      document.removeEventListener('click', closeProfileMenuOnOutsideClick);
    }

    function closeProfileMenuOnOutsideClick(e) {
      const dropdown = document.getElementById('profileDropdown');
      if (dropdown && !dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleProfileMenu"]')) {
        closeProfileMenu();
      }
    }

    // Currency selection
    function setCurrency(currency) {
      // Remove active state from all currency buttons
      const currencyButtons = ['currency-usd', 'currency-eur', 'currency-rd'];
      currencyButtons.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
          btn.style.background = '#fff';
          btn.style.color = '#374151';
          btn.style.borderColor = '#d1d5db';
        }
      });
      
      // Set active state for selected currency
      const activeBtn = document.getElementById('currency-' + currency.toLowerCase());
      if (activeBtn) {
        activeBtn.style.background = '#111827';
        activeBtn.style.color = '#fff';
        activeBtn.style.borderColor = '#111827';
      }
      
      // Store currency preference
      localStorage.setItem('selectedCurrency', currency);
      
      // Update currency display throughout the page
      updateCurrencyDisplay(currency);
    }

    // Language selection
    function setLanguage(lang) {
      // Remove active state from all language buttons
      const langButtons = ['lang-es', 'lang-en'];
      const indicator = document.getElementById('lang-indicator');
      
      langButtons.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
          btn.style.opacity = '0.52';
          const textSpan = btn.querySelector('span');
          const svg = btn.querySelector('svg');
          if (textSpan) {
            textSpan.style.color = '#717784';
          }
          if (svg) {
            svg.style.color = '#717784';
          }
        }
      });
      
      // Set active state for selected language
      const activeBtn = document.getElementById('lang-' + lang);
      if (activeBtn) {
        activeBtn.style.opacity = '1';
        const textSpan = activeBtn.querySelector('span');
        const svg = activeBtn.querySelector('svg');
        if (textSpan) {
          textSpan.style.color = '#525866';
        }
        if (svg) {
          svg.style.color = '#525866';
        }
        
        // Move indicator
        if (indicator) {
          if (lang === 'es') {
            indicator.style.left = '4px';
          } else {
            indicator.style.left = '156px';
          }
        }
      }
      
      // Store language preference
      localStorage.setItem('selectedLanguage', lang);
      
      // Update language display throughout the page
      updateLanguageDisplay(lang);
    }

    // Update currency display
    function updateCurrencyDisplay(currency) {
      // This function can be expanded to update all price displays
      console.log('Currency changed to:', currency);
      // You can add logic here to update prices throughout the page
    }

    // Update language display
    function updateLanguageDisplay(lang) {
      // This function can be expanded to update all text content
      console.log('Language changed to:', lang);
      // You can add logic here to update text throughout the page
    }

    // Initialize currency and language on page load
    document.addEventListener('DOMContentLoaded', function() {
      const savedCurrency = localStorage.getItem('selectedCurrency') || 'USD';
      const savedLanguage = localStorage.getItem('selectedLanguage') || 'en';
      
      setCurrency(savedCurrency);
      setLanguage(savedLanguage);
    });

    // Buyer profile toggle (Para Vivir / Para Invertir)
    function setBuyerProfile(profile) {
      const btnVivir = document.getElementById('btnProfileVivir');
      const btnInvertir = document.getElementById('btnProfileInvertir');
      const financial = document.getElementById('financialBlock');
      const lifestyle = document.getElementById('lifestyleBlock');
      if (!btnVivir || !btnInvertir) return;
      const activeStyle = 'background:rgb(102,123,106);color:white;';
      const inactiveStyle = 'background:transparent;color:rgb(98,84,65);';
      const baseStyle = 'border:none;padding:3px 10px;border-radius:9999px;cursor:pointer;font-weight:600;';
      if (profile === 'vivir') {
        btnVivir.style.cssText = baseStyle + activeStyle;
        btnInvertir.style.cssText = baseStyle + inactiveStyle;
        if (financial) financial.style.display = 'none';
        if (lifestyle) lifestyle.style.display = 'block';
      } else {
        btnInvertir.style.cssText = baseStyle + activeStyle;
        btnVivir.style.cssText = baseStyle + inactiveStyle;
        if (financial) financial.style.display = 'flex';
        if (lifestyle) lifestyle.style.display = 'none';
      }
    }

    function openVideoCall() {
      const unitNum = document.getElementById('modalUnitNum')?.textContent || '';
      const subject = encodeURIComponent('Agendar videollamada - Unidad ' + unitNum + ' Makai Residences');
      window.location.href = 'mailto:support+makai_residences@launchbase.co.za?subject=' + subject;
    }

    function openAdvisorVideoCall(unitId) {
      document.getElementById('advisorModalUnitId').value = unitId || '';
      document.getElementById('advisorModal').style.display = 'flex';
    }
    function closeAdvisorVideoCall() {
      document.getElementById('advisorModal').style.display = 'none';
    }
    function submitAdvisorVideoCall(e) {
      e.preventDefault();
      const form = e.target;
      const data = {
        unit_id: form.unit_id.value,
        name: form.name.value,
        email: form.email.value,
        phone: form.phone.value,
        preferred_time: form.preferred_time.value,
      };
      const subject = encodeURIComponent('Solicitud de videollamada - Makai Residences');
      const body = encodeURIComponent(
        'Nombre: ' + data.name + '\n' +
        'Email: ' + data.email + '\n' +
        'Teléfono: ' + data.phone + '\n' +
        'Horario preferido: ' + data.preferred_time + '\n' +
        'Unidad de interés: ' + (data.unit_id || 'No especificada')
      );
      window.location.href = 'mailto:support+makai_residences@launchbase.co.za?subject=' + subject + '&body=' + body;
      closeAdvisorVideoCall();
      return false;
    }

    function openWhatsAppBroker() {
      const unitNum = document.getElementById('modalUnitNum')?.textContent || '';
      const price = document.getElementById('modalPrice')?.textContent || '';
      const text = encodeURIComponent('Hola, tengo interés en la Unidad ' + unitNum + ' de Makai Residences (' + price + '). ¿Podemos hablar?');
      window.open('https://wa.me/?text=' + text, '_blank');
    }

    function shareWithCoInvestor() {
      const unitNum = document.getElementById('modalUnitNum')?.textContent || '';
      const subject = encodeURIComponent('Makai Residences - Unidad ' + unitNum);
      const body = encodeURIComponent('Te comparto esta unidad para que la veamos juntos: ' + window.location.href);
      window.location.href = 'mailto:?subject=' + subject + '&body=' + body;
    }

    // Opens the property PDF in a new tab; that page auto-triggers the browser
    // print dialog so the user can "Save as PDF" / download.
    function sharePropertyPdf() {
      if (typeof currentOpenUnit === 'undefined' || !currentOpenUnit) {
        alert('Primero abrí los detalles de una unidad.');
        return;
      }
      const recipient = prompt('¿Para quién es esta ficha? (opcional)', '');
      const params = new URLSearchParams();
      if (recipient && recipient.trim()) params.set('to', recipient.trim());
      const qs = params.toString();
      const url = '/property-pdf/' + encodeURIComponent(currentOpenUnit) + (qs ? '?' + qs : '');
      window.open(url, '_blank', 'noopener');
    }

    function toggleAlerts() {
      alert('Alertas activadas. Te avisamos si baja de precio o quedan menos de 3 unidades.');
    }

    // ============================
    // FILTER FUNCTIONALITY
    // ============================
    let currentFilters = {
      unitNumber: '',
      minPrice: null,
      maxPrice: null,
      types: [],
      directions: [],
      outlooks: [],
      floors: [],
      sort: 'custom_id'
    };

    // Toggle filter dropdown
    function toggleFilterDropdown(filterType) {
      const dropdown = document.getElementById(filterType + 'Dropdown');
      if (!dropdown) return;
      
      // Close all other dropdowns first
      document.querySelectorAll('.filter-dropdown').forEach(d => {
        if (d !== dropdown) d.style.display = 'none';
      });
      
      // Toggle current dropdown
      dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
      
      // Close on outside click
      if (dropdown.style.display === 'block') {
        setTimeout(() => {
          document.addEventListener('click', function closeDropdown(e) {
            if (!dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleFilterDropdown"]')) {
              dropdown.style.display = 'none';
              document.removeEventListener('click', closeDropdown);
            }
          });
        }, 10);
      }
    }

    // Apply price filter
    function applyPriceFilter() {
      const minPrice = document.getElementById('minPrice').value;
      const maxPrice = document.getElementById('maxPrice').value;
      
      currentFilters.minPrice = minPrice ? parseFloat(minPrice) : null;
      currentFilters.maxPrice = maxPrice ? parseFloat(maxPrice) : null;
      
      updatePriceLabel();
      toggleFilterDropdown('price');
      applyFilters();
    }

    // Apply type filter
    function applyTypeFilter() {
      const checkboxes = document.querySelectorAll('#typeDropdown input[type="checkbox"]:checked');
      currentFilters.types = Array.from(checkboxes).map(cb => cb.value);
      updateTypeLabel();
      applyFilters();
    }

    // Apply direction filter
    function applyDirectionFilter() {
      const checkboxes = document.querySelectorAll('#directionDropdown input[type="checkbox"]:checked');
      currentFilters.directions = Array.from(checkboxes).map(cb => cb.value);
      updateDirectionLabel();
      applyFilters();
    }

    // Apply outlook filter
    function applyOutlookFilter() {
      const checkboxes = document.querySelectorAll('#outlookDropdown input[type="checkbox"]:checked');
      currentFilters.outlooks = Array.from(checkboxes).map(cb => cb.value);
      updateOutlookLabel();
      applyFilters();
    }

    // Apply floor filter
    function applyFloorFilter() {
      const checkboxes = document.querySelectorAll('#floorDropdown input[type="checkbox"]:checked');
      currentFilters.floors = Array.from(checkboxes).map(cb => cb.value);
      updateFloorLabel();
      applyFilters();
    }

    // Apply sort filter
    function applySortFilter() {
      const radio = document.querySelector('#sortDropdown input[type="radio"]:checked');
      if (radio) {
        currentFilters.sort = radio.value;
        updateSortLabel();
        applyFilters();
      }
    }

    // Update filter labels
    function updatePriceLabel() {
      const label = document.getElementById('priceLabel');
      if (currentFilters.minPrice || currentFilters.maxPrice) {
        const min = currentFilters.minPrice ? `$${number_format(currentFilters.minPrice, 0)}` : 'Any';
        const max = currentFilters.maxPrice ? `$${number_format(currentFilters.maxPrice, 0)}` : 'Any';
        label.textContent = `${min} - ${max}`;
      } else {
        label.textContent = 'Price';
      }
    }

    function updateTypeLabel() {
      const label = document.getElementById('typeLabel');
      label.textContent = currentFilters.types.length > 0 ? `Types (${currentFilters.types.length})` : 'Unit Type';
    }

    function updateDirectionLabel() {
      const label = document.getElementById('directionLabel');
      label.textContent = currentFilters.directions.length > 0 ? `Directions (${currentFilters.directions.length})` : 'Direction';
    }

    function updateOutlookLabel() {
      const label = document.getElementById('outlookLabel');
      label.textContent = currentFilters.outlooks.length > 0 ? `Outlooks (${currentFilters.outlooks.length})` : 'Outlook';
    }

    function updateFloorLabel() {
      const label = document.getElementById('floorLabel');
      label.textContent = currentFilters.floors.length > 0 ? `Floors (${currentFilters.floors.length})` : 'Floor';
    }

    function updateSortLabel() {
      const label = document.getElementById('sortLabel');
      const sortOptions = {
        'price-asc': 'Price ↑',
        'price-desc': 'Price ↓',
        'size-asc': 'Size ↑',
        'size-desc': 'Size ↓',
        'bedrooms-asc': 'Beds ↑',
        'bedrooms-desc': 'Beds ↓',
        'custom_id': 'Unit #'
      };
      label.textContent = sortOptions[currentFilters.sort] || 'Sort';
    }

    // Apply all filters via AJAX
    function applyFilters() {
      const unitNumberInput = document.querySelector('input[placeholder="Unit No."]');
      if (unitNumberInput) {
        currentFilters.unitNumber = unitNumberInput.value;
      }

      // Show loading state
      showFilterLoading();

      // Send AJAX request
      fetch('/api/units/filter', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(currentFilters)
      })
      .then(response => response.json())
      .then(data => {
        updateUnitsGrid(data.units);
        updateMatchCount(data.total);
        hideFilterLoading();
      })
      .catch(error => {
        console.error('Error applying filters:', error);
        hideFilterLoading();
      });
    }

    // Update units grid
    function updateUnitsGrid(units) {
      const gridContainer = document.querySelector('.grid-template-columns');
      if (!gridContainer) return;

      gridContainer.innerHTML = '';
      
      units.forEach(unit => {
        const unitCard = createUnitCard(unit);
        gridContainer.appendChild(unitCard);
      });
    }

    // Create unit card HTML
    function createUnitCard(unit) {
      const div = document.createElement('div');
      div.style.cssText = 'position:relative;width:100%;max-width:24rem;overflow:hidden;background:rgb(249,248,246);border-radius:1rem;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1),0 4px 6px -4px rgba(0,0,0,0.1);';
      
      // Status overlay
      if (unit.status === 'sold' || unit.status === 'pending') {
        const statusDiv = document.createElement('div');
        statusDiv.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(67,58,45,0.8);z-index:20;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding-top:7rem;cursor:not-allowed;';
        statusDiv.innerHTML = `<span style="color:white;font-size:1.5rem;font-weight:700;letter-spacing:0.15em;">${unit.status.toUpperCase()}</span>`;
        div.appendChild(statusDiv);
      }

      const isAvailable = unit.status !== 'sold' && unit.status !== 'pending';
      const viewers = Math.floor(Math.random() * 11) + 8;

      // Image section
      const imageSection = document.createElement('div');
      imageSection.style.cssText = 'min-height:14rem;';
      imageSection.innerHTML = `
        <div style="position:relative;overflow:hidden;">
          ${unit.images && unit.images.length > 0
            ? `<img src="${unit.images[0].path}" alt="Unit Render" style="width:100%;object-fit:cover;display:block;${unit.status === 'sold' ? 'filter:grayscale(20%);' : ''}" onerror="this.style.height='200px';this.style.background='rgb(218,211,200)'">`
            : '<div style="width:100%;height:200px;background:rgb(218,211,200);display:flex;align-items:center;justify-content:center;color:rgb(98,84,65);">No Image Available</div>'
          }
          ${isAvailable
            ? `<div class="live-viewers" style="position:absolute;top:0.5rem;left:0.5rem;background:rgba(0,0,0,0.55);color:white;font-size:0.62rem;font-weight:600;padding:3px 8px;border-radius:9999px;display:flex;align-items:center;gap:5px;letter-spacing:0.02em;">
                 <span style="width:6px;height:6px;border-radius:50%;background:rgb(34,197,94);" class="advisor-dot"></span>
                 <span>${viewers} viendo ahora</span>
               </div>`
            : ''
          }
        </div>
      `;
      div.appendChild(imageSection);

      // Details section
      const pricePerSqft = unit.internal_area && unit.internal_area > 0
        ? `<span style="display:block;font-size:0.62rem;color:rgb(98,84,65);opacity:0.75;line-height:1.2;">$${number_format(Math.round(unit.price / unit.internal_area), 0)}/sqft <span style="color:rgb(34,197,94);font-weight:600;">· -12% mercado</span></span>`
        : '';
      const detailsSection = document.createElement('div');
      detailsSection.style.cssText = 'position:relative;padding:1.25rem 1.5rem 0.5rem 1.5rem;';
      detailsSection.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div style="text-align:left;">
            <span style="display:block;font-weight:700;color:rgb(98,84,65);font-size:1rem;">${unit.custom_id || unit.name}</span>
            <span style="display:block;font-weight:600;font-size:0.875rem;color:rgb(98,84,65);">${unit.floor ? ucfirst(unit.floor) : 'Ground Floor'}</span>
            <span style="font-size:0.8rem;color:rgb(98,84,65);max-width:10rem;display:block;">${unit.bedrooms} Bed ${unit.bathrooms ? '| ' + unit.bathrooms + ' Bath' : ''} ${unit.direction ? '| ' + unit.direction.toUpperCase() : ''} ${unit.outlook ? '| ' + unit.outlook : ''}</span>
          </div>
          <div style="text-align:right;position:absolute;right:1.25rem;top:1.25rem;">
            ${unit.status === 'sold' || unit.status === 'pending'
              ? `<span style="font-size:1rem;font-weight:800;color:rgb(98,84,65);">${unit.status.toUpperCase()}</span>`
              : `<div style="display:inline-block;text-align:right;">
                  <span style="display:block;font-weight:800;background:linear-gradient(to bottom right,rgb(210,182,144),rgb(107,80,43));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-size:1.1rem;">$${number_format(unit.price, 0, ' ', ' ')}</span>
                  ${pricePerSqft}
                  <span style="display:block;font-size:0.6rem;color:rgb(180,134,72);font-weight:600;line-height:1.2;margin-top:1px;">Reserva desde $5000</span>
                  ${unit.discount && unit.discount > 0
                    ? `<button title="Válido hasta el 30 de abril" style="background:rgb(34,197,94);color:white;font-size:0.65rem;border:none;border-radius:4px;padding:2px 6px;cursor:pointer;line-height:1.4;font-weight:600;margin-top:3px;">UNLOCK <b>$${number_format(unit.discount, 0, ' ', ' ')}</b><br>DISCOUNT</button>`
                    : ''
                  }
                </div>`
            }
          </div>
        </div>
      `;
      div.appendChild(detailsSection);

      // Action buttons
      const actionButtons = document.createElement('div');
      actionButtons.style.cssText = 'display:flex;flex-direction:row;';
      const rightButton = unit.status === 'sold'
        ? `<button style="width:50%;height:2.5rem;background:rgb(98,84,65);color:white;border:none;font-size:0.75rem;font-weight:600;letter-spacing:0.08em;cursor:not-allowed;opacity:0.9;">SOLD</button>`
        : unit.status === 'pending'
        ? `<button style="width:50%;height:2.5rem;background:rgb(180,180,180);color:white;border:none;font-size:0.75rem;font-weight:600;letter-spacing:0.08em;cursor:not-allowed;">PENDING</button>`
        : `<button onclick="openReservePage('${unit.id}')" style="width:50%;height:2.5rem;background:rgb(102,123,106);color:white;border:none;font-size:0.75rem;font-weight:600;letter-spacing:0.08em;cursor:pointer;">RESERVE</button>`;
      actionButtons.innerHTML = `
        <button onclick="openMoreInfo('${unit.id}')" style="width:50%;height:2.5rem;background:rgb(239,235,230);color:rgb(98,84,65);border:none;font-size:0.75rem;font-weight:600;letter-spacing:0.08em;cursor:pointer;">MORE INFO</button>
        ${rightButton}
      `;
      div.appendChild(actionButtons);

      // Advisor live + video call CTA
      if (isAvailable) {
        const advisorBlock = document.createElement('div');
        advisorBlock.style.cssText = 'padding:0.5rem 0.75rem 0.75rem 0.75rem;display:flex;flex-direction:column;align-items:center;gap:0.4rem;';
        advisorBlock.innerHTML = `
          <div class="advisor-live" style="display:flex;align-items:center;gap:6px;font-size:0.7rem;color:rgb(34,150,80);font-weight:600;">
            <span class="advisor-dot" style="width:7px;height:7px;border-radius:50%;background:rgb(34,197,94);"></span>
            <span>Tu asesor está disponible ahora mismo</span>
          </div>
          <button onclick="openAdvisorVideoCall('${unit.id}')" style="display:inline-flex;align-items:center;gap:6px;background:white;color:rgb(98,84,65);border:1px solid rgb(218,211,200);border-radius:0.5rem;padding:5px 12px;font-size:0.72rem;font-weight:600;cursor:pointer;letter-spacing:0.02em;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="rgb(180,134,72)"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
            Agendar una Videollamada
          </button>
        `;
        div.appendChild(advisorBlock);
      }

      return div;
    }

    // Update match count
    function updateMatchCount(count) {
      const matchButton = document.querySelector('button[style*="rgb(34,197,94)"]');
      if (matchButton) {
        matchButton.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M21,9L17,5V8H10V10H17V13M7,11L3,15L7,19V16H14V14H7V11Z"></path>
          </svg>
          ${count} Matches
        `;
      }
    }

    // Loading states
    function showFilterLoading() {
      const gridContainer = document.querySelector('.grid-template-columns');
      if (gridContainer) {
        gridContainer.style.opacity = '0.5';
      }
    }

    function hideFilterLoading() {
      const gridContainer = document.querySelector('.grid-template-columns');
      if (gridContainer) {
        gridContainer.style.opacity = '1';
      }
    }

    // Reset filters
    function resetFilters() {
      currentFilters = {
        unitNumber: '',
        minPrice: null,
        maxPrice: null,
        types: [],
        directions: [],
        outlooks: [],
        floors: [],
        sort: 'custom_id'
      };

      // Reset form elements
      document.getElementById('minPrice').value = '';
      document.getElementById('maxPrice').value = '';
      document.querySelectorAll('#typeDropdown input[type="checkbox"]').forEach(cb => cb.checked = false);
      document.querySelectorAll('#directionDropdown input[type="checkbox"]').forEach(cb => cb.checked = false);
      document.querySelectorAll('#outlookDropdown input[type="checkbox"]').forEach(cb => cb.checked = false);
      document.querySelectorAll('#floorDropdown input[type="checkbox"]').forEach(cb => cb.checked = false);
      document.querySelector('input[placeholder="Unit No."]').value = '';
      document.querySelector('#sortDropdown input[value="custom_id"]').checked = true;

      // Update labels
      updatePriceLabel();
      updateTypeLabel();
      updateDirectionLabel();
      updateOutlookLabel();
      updateFloorLabel();
      updateSortLabel();

      // Apply filters
      applyFilters();
    }

    // Helper function
    function number_format(number, decimals, dec_point, thousands_sep) {
      if (decimals === 0) {
        thousands_sep = thousands_sep || ',';
        return Math.floor(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);
      }
      // Full implementation for decimal cases if needed
      return number.toLocaleString();
    }

    function ucfirst(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Add event listener for reset button
    document.addEventListener('DOMContentLoaded', function() {
      const resetButton = document.querySelector('button[style*="rgb(239,68,68)"]');
      if (resetButton) {
        resetButton.addEventListener('click', resetFilters);
      }

      // Add event listener for unit number search
      const unitNumberInput = document.querySelector('input[placeholder="Unit No."]');
      if (unitNumberInput) {
        unitNumberInput.addEventListener('input', function() {
          currentFilters.unitNumber = this.value;
          applyFilters();
        });
      }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeMoreInfo();
        closeReservePage();
        closeMenu();
        closeProjects();
        closeProfileMenu();
        // Close all filter dropdowns
        document.querySelectorAll('.filter-dropdown').forEach(d => d.style.display = 'none');
      }
    });

    // ============================
    // WISHLIST TOGGLE — hearts on cards persist to the DB for logged-in users
    // ============================
    (function initWishlist(){
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      document.addEventListener('click', function(e){
        const btn = e.target.closest('[data-wishlist-toggle]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        const unitId = btn.dataset.unitId;
        if (!unitId) return;

        // Optimistic UI flip
        const wasFav = btn.classList.contains('is-fav');
        btn.classList.toggle('is-fav', !wasFav);
        const svg = btn.querySelector('svg');
        if (svg) svg.setAttribute('fill', !wasFav ? 'currentColor' : 'none');
        const label = btn.querySelector('.label');
        if (label) label.textContent = !wasFav ? 'Saved' : 'Add to list';

        fetch(`/api/wishlist/toggle/${unitId}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          credentials: 'same-origin',
        }).then(r => r.ok ? r.json() : Promise.reject(r))
          .then(data => {
            if (data && data.success) {
              // Sync to authoritative state in case of race
              btn.classList.toggle('is-fav', !!data.wishlisted);
              if (svg) svg.setAttribute('fill', data.wishlisted ? 'currentColor' : 'none');
              if (label) label.textContent = data.wishlisted ? 'Saved' : 'Add to list';
              const cnt = document.querySelector(`[data-unit-count="${unitId}"]`);
              if (cnt && typeof data.unit_count !== 'undefined') cnt.textContent = data.unit_count;
              const headerCnt = document.querySelector('[data-saved-count]');
              if (headerCnt && typeof data.total !== 'undefined') headerCnt.textContent = `Guardados (${data.total})`;
            }
          }).catch(err => {
            // Revert optimistic UI
            btn.classList.toggle('is-fav', wasFav);
            if (svg) svg.setAttribute('fill', wasFav ? 'currentColor' : 'none');
            if (label) label.textContent = wasFav ? 'Saved' : 'Add to list';
            if (err && err.status === 401) {
              window.location.href = '/login';
            }
          });
      });
    })();

    // ============================
    // RESERVED CARD COUNTDOWN (HH:MM:SS)
    // ============================
    (function initReservedCountdowns(){
      function pad(n){ return n.toString().padStart(2, '0'); }
      function tick(){
        const now = Date.now();
        document.querySelectorAll('[data-reserved-until]').forEach(el => {
          const until = Date.parse(el.getAttribute('data-reserved-until'));
          const target = el.querySelector('[data-countdown]');
          if (!target || isNaN(until)) return;
          let diff = Math.max(0, Math.floor((until - now) / 1000));
          const h = Math.floor(diff / 3600);
          const m = Math.floor((diff % 3600) / 60);
          const s = diff % 60;
          target.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
          if (diff === 0) el.removeAttribute('data-reserved-until');
        });
      }
      tick();
      setInterval(tick, 1000);
    })();

    // ============================
    // GRID / LIST / PLAN TOGGLE
    // ============================
    function setViewMode(view) {
      const allButtons = document.querySelectorAll('.fg-toggle button[data-view], .fg-location-btn[data-view]');
      allButtons.forEach(b => {
        const isActive = b.dataset.view === view;
        b.classList.toggle('active', isActive);
        b.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
      const toggle = document.querySelector('.fg-toggle');
      if (toggle) {
        toggle.classList.toggle('list-active', view === 'list');
        toggle.classList.toggle('plan-active', view === 'plan');
      }
      // Drive show/hide via body[data-view] for grid/list/plan
      document.body.setAttribute('data-view', view);
    }

    document.querySelectorAll('.fg-toggle button[data-view], .fg-location-btn[data-view]').forEach(btn => {
      btn.addEventListener('click', function() {
        setViewMode(this.dataset.view);
      });
    });

    // List view tabs
    function setListTab(btn) {
      const tab = btn.dataset.tab;
      document.querySelectorAll('.fg-list-tab').forEach(t => t.classList.remove('active'));
      btn.classList.add('active');
      const rows = document.querySelectorAll('#fgListTable tbody tr');
      rows.forEach(r => {
        if (tab === 'all') { r.style.display = ''; return; }
        r.style.display = (r.dataset.tab === tab) ? '' : 'none';
      });
    }
    function filterListRows(q) {
      const needle = (q || '').trim().toLowerCase();
      document.querySelectorAll('#fgListTable tbody tr').forEach(r => {
        const hay = (r.dataset.search || '');
        r.style.display = !needle || hay.includes(needle) ? '' : 'none';
      });
    }
  </script>


</body>

</html>
