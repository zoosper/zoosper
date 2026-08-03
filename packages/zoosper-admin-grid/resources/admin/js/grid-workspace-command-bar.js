(() => {
    'use strict';
    const settings=document.querySelector('[data-grid-settings]');
    const toggle=document.querySelector('[data-grid-settings-toggle]');
    const close=settings?.querySelector('[data-grid-settings-close]');
    if(!settings){if(toggle)toggle.hidden=true;return;}
    const dismiss=(focus=false)=>{settings.hidden=true;settings.open=false;settings.removeAttribute('style');toggle?.setAttribute('aria-expanded','false');if(focus)toggle?.focus();};
    const position=()=>{
        if(!toggle||settings.hidden)return;
        const trigger=toggle.getBoundingClientRect();
        const workspace=toggle.closest('[data-grid-workspace]')?.getBoundingClientRect();
        const margin=16,gap=8;
        const boundaryLeft=Math.max(margin,workspace?.left??margin);
        const boundaryRight=Math.min(window.innerWidth-margin,workspace?.right??window.innerWidth-margin);
        const available=Math.max(320,boundaryRight-boundaryLeft);
        const width=Math.min(680,available);
        const preferred=trigger.left;
        const left=Math.min(Math.max(boundaryLeft,preferred),boundaryRight-width);
        settings.style.setProperty('--grid-settings-left',`${Math.max(boundaryLeft,left)}px`);
        settings.style.setProperty('--grid-settings-top',`${trigger.bottom+gap}px`);
        settings.style.setProperty('--grid-settings-width',`${width}px`);
    };
    toggle?.addEventListener('click',()=>{if(!settings.hidden){dismiss(true);return;}document.querySelectorAll('[data-grid-panel]').forEach(p=>p.hidden=true);document.querySelectorAll('[data-grid-toggle]').forEach(b=>b.setAttribute('aria-expanded','false'));settings.hidden=false;settings.open=true;toggle.setAttribute('aria-expanded','true');position();settings.querySelector('input[name="view_name"]')?.focus();});
    close?.addEventListener('click',()=>dismiss(true));window.addEventListener('resize',position);window.addEventListener('scroll',position,true);
    document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!settings.hidden)dismiss(true);});
    document.addEventListener('click',e=>{const t=e.target;if(!(t instanceof Node)||settings.contains(t)||toggle?.contains(t))return;dismiss();});
})();
