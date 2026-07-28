<button class="btn btn-back-saas" type="button" onclick="window.history.back();">
  <span class="btn-back-ico" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
      <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l4.147 4.146a.5.5 0 0 1-.708.708l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 1 1 .708.708L2.707 7.5H14.5a.5.5 0 0 1 .5.5z"/>
    </svg>
  </span>
  <span class="btn-back-txt">Atrás</span>
</button>

<style>
  :root{
    --au-primary:#20427F;
    --au-primary-dark:#132b52;
    --au-ink:#0f172a;
    --au-muted:#64748b;

    --au-radius: 999px;
    --au-border: 1px solid rgba(2,6,23,.12);
    --au-shadow: 0 10px 26px rgba(2,6,23,.10);
  }

  /* ✅ Botón SaaS */
  .btn-back-saas{
    -webkit-tap-highlight-color: transparent;
    appearance: none;
    border: var(--au-border);
    background: rgba(255,255,255,.92);
    color: var(--au-ink);

    border-radius: var(--au-radius);
    padding: 8px 14px;
    font-weight: 900;
    font-size: 14px;

    display: inline-flex;
    align-items: center;
    gap: 10px;

    box-shadow: 0 2px 10px rgba(2,6,23,.08);
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
  }

  .btn-back-saas .btn-back-ico{
    width: 34px;
    height: 34px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;

    background: rgba(32,66,127,.10);
    border: 1px solid rgba(32,66,127,.14);
    color: var(--au-primary);
    transition: transform .18s ease, background .18s ease;
    flex: 0 0 auto;
  }

  .btn-back-saas .btn-back-txt{
    letter-spacing: .2px;
    line-height: 1;
  }

  /* Hover / Active */
  .btn-back-saas:hover{
    background: #fff;
    border-color: rgba(32,66,127,.20);
    box-shadow: var(--au-shadow);
    transform: translateY(-1px);
    color: var(--au-ink);
  }

  .btn-back-saas:hover .btn-back-ico{
    transform: translateX(-2px);
    background: rgba(32,66,127,.12);
  }

  .btn-back-saas:active{
    transform: translateY(0px);
    box-shadow: 0 2px 10px rgba(2,6,23,.08);
  }

  .btn-back-saas:focus{
    outline: none;
  }
  .btn-back-saas:focus-visible{
    box-shadow: 0 0 0 4px rgba(32,66,127,.18), 0 2px 10px rgba(2,6,23,.08);
    border-color: rgba(32,66,127,.35);
  }

  /* ✅ Mobile: un poquito más compacto */
  @media (max-width: 576px){
    .btn-back-saas{
      padding: 7px 12px;
      font-size: 13px;
      gap: 8px;
    }
    .btn-back-saas .btn-back-ico{
      width: 32px;
      height: 32px;
    }
  }

  /* Tu breadcrumb lo dejamos igual */
  .breadcrumb{
    background-color: transparent !important;
    padding: 0;
    margin: .25rem 0 .5rem 0;
  }
</style>
