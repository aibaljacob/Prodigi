(function(){
  function byId(id){ return document.getElementById(id); }
  function setError(el, msg){
    if(!el) return;
    el.classList.remove('input-valid');
    el.classList.add('input-error');
    const hint = byId(el.id + '_error');
    if(hint){ hint.textContent = msg || ''; hint.style.display = msg ? 'block' : 'none'; }
  }
  function setValid(el){
    if(!el) return;
    el.classList.remove('input-error');
    el.classList.add('input-valid');
    const hint = byId(el.id + '_error');
    if(hint){ hint.textContent = ''; hint.style.display = 'none'; }
  }
  function isSpacesOnly(v){ return v != null && v.trim().length === 0; }
  function isRepeatedChars(v){ return /^(.)\1{2,}$/.test(v); }
  function hasMixedCase(v){ return /[a-z]/.test(v) && /[A-Z]/.test(v); }
  function hasNumber(v){ return /\d/.test(v); }
  function hasSpecial(v){ return /[^A-Za-z0-9]/.test(v); }
  function hasWhitespace(v){ return /\s/.test(v); }
  function looksLikeEmail(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
  function isUsernameValid(v){ return /^[A-Za-z0-9_]{3,20}$/.test(v); }
  function isPhoneValid(v){ return v === '' || /^[+]?\d{7,15}$/.test(v.replace(/\s|-/g,'')); }

  function validateLogin(){
    const u = byId('login_username');
    const p = byId('login_password');
    const submit = byId('login_submit');
    let ok = true;

    // Username or email
    if(u) u.value = (u.value || '').trim();
    if(!u || isSpacesOnly(u.value) || isRepeatedChars(u.value)){
      setError(u, 'Enter a valid username or email.'); ok = false;
    } else {
      // allow either valid username or email
      if(!isUsernameValid(u.value) && !looksLikeEmail(u.value)){
        setError(u, 'Use a valid username or email.'); ok = false;
      } else { setValid(u); }
    }

    // Password: no whitespace, min length 6
    if(p) p.value = (p.value || '').trim();
    if(!p || isSpacesOnly(p.value) || p.value.length < 6 || hasWhitespace(p.value)){
      setError(p, 'Password must be at least 6 characters and contain no spaces.'); ok = false;
    } else { setValid(p); }

    if(submit) submit.disabled = !ok;
    return ok;
  }

  function normalizeSpaces(v){ return (v || '').replace(/\s+/g,' ').trim(); }

  function validateRegister(){
    const u = byId('reg_username');
    const e = byId('reg_email');
    const f = byId('reg_fullname');
  const ph = null;
    const p = byId('reg_password');
    const c = byId('reg_confirm');
    const submit = byId('reg_submit');
    let ok = true;

    // Username
    if(u) u.value = (u.value || '').trim();
    if(!u || isSpacesOnly(u.value) || isRepeatedChars(u.value) || !isUsernameValid(u.value)){
      setError(u, '3-20 chars, letters, numbers, underscore.'); ok = false;
    } else { setValid(u); }

    // Email
    if(e) e.value = (e.value || '').trim();
    if(!e || isSpacesOnly(e.value) || !looksLikeEmail(e.value)){
      setError(e, 'Enter a valid email.'); ok = false;
    } else { setValid(e); }

    // Full name: letters and spaces only, min length 2
    // Use normalized copy for validation but don't mutate the field during typing
    const fRaw = f ? f.value : '';
    const fNorm = normalizeSpaces(fRaw);
    if(!f || isSpacesOnly(fNorm) || isRepeatedChars(fNorm) || fNorm.length < 2 || !/^[A-Za-z ]+$/.test(fNorm)){
      setError(f, 'Only letters and spaces are allowed.'); ok = false;
    } else { setValid(f); }

    // Phone removed

    // Password complexity: 8+, mixed case, number, special, not repeated, no whitespace
    if(!p || p.value.length < 8 || hasWhitespace(p.value) || !hasMixedCase(p.value) || !hasNumber(p.value) || !hasSpecial(p.value) || isRepeatedChars(p.value)){
      setError(p, '8+ chars with upper, lower, number, special, and no spaces.'); ok = false;
    } else { setValid(p); }

    // Confirm
    if(!c || c.value !== (p ? p.value : '')){
      setError(c, 'Passwords must match.'); ok = false;
    } else { setValid(c); }

    if(submit) submit.disabled = !ok;
    return ok;
  }

  function attach(){
    const loginForm = byId('loginForm');
    const regForm = byId('registerForm');

    if(loginForm){
      ['login_username','login_password'].forEach(id => {
        const el = byId(id); if(el){
          el.addEventListener('input', validateLogin);
          el.addEventListener('blur', validateLogin);
        }
      });
      // Block space in password field
      const lp = byId('login_password');
      if(lp){ lp.addEventListener('keydown', (e) => { if(e.key === ' ') e.preventDefault(); }); }
      loginForm.addEventListener('submit', function(e){ if(!validateLogin()){ e.preventDefault(); }});
      validateLogin();
    }

    if(regForm){
      ['reg_username','reg_email','reg_fullname','reg_password','reg_confirm'].forEach(id => {
        const el = byId(id); if(el){ el.addEventListener('input', validateRegister); el.addEventListener('blur', validateRegister); }
      });
      // Block space in password fields
      const rp = byId('reg_password');
      const rc = byId('reg_confirm');
      if(rp){ rp.addEventListener('keydown', (e) => { if(e.key === ' ') e.preventDefault(); }); }
      if(rc){ rc.addEventListener('keydown', (e) => { if(e.key === ' ') e.preventDefault(); }); }
      // Normalize on submit to prevent hidden leading/trailing/multi spaces
      regForm.addEventListener('submit', function(){
        const f = byId('reg_fullname'); if(f){ f.value = normalizeSpaces(f.value); }
        const e = byId('reg_email'); if(e){ e.value = (e.value||'').trim(); }
        const u = byId('reg_username'); if(u){ u.value = (u.value||'').trim(); }
      });
      regForm.addEventListener('submit', function(e){ if(!validateRegister()){ e.preventDefault(); }});
      validateRegister();
    }
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', attach);
  } else { attach(); }
})();