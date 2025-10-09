<script>
(function(){
  // --- safe cookies (без RegExp) ---
  function setCookie(name, value, maxAgeSec) {
    var parts = [
      name + '=' + encodeURIComponent(value),
      'Max-Age=' + maxAgeSec,
      'Path=/',
      'Domain=.dream-v-doma.com.ua', // за потреби прибери, якщо локалка
      'SameSite=Lax'
      // 'Secure'  // додай на HTTPS у проді
    ];
    document.cookie = parts.join('; ');
  }

  function getCookie(name) {
    var s = document.cookie || '';
    if (!s) return '';
    var arr = s.split('; ');
    for (var i = 0; i < arr.length; i++) {
      var idx = arr[i].indexOf('=');
      var k = idx > -1 ? arr[i].slice(0, idx) : arr[i];
      if (k === name) {
        return decodeURIComponent(idx > -1 ? arr[i].slice(idx + 1) : '');
      }
    }
    return '';
  }

  var q   = (location.search   || '').toLowerCase();
  var ref = (document.referrer || '').toLowerCase();
  var ua  = (navigator.userAgent|| '').toLowerCase();

  // 1) перший хіт із TikTok → ставимо мітку
  if (q.indexOf('ttclid=') !== -1 || ref.indexOf('tiktok') !== -1 || ua.indexOf('tiktok') !== -1) {
    setCookie('_mp_src', 'tiktok', 60*60); // 60 хв
  }

  // 2) продовжуємо життя мітки
  if (getCookie('_mp_src') === 'tiktok') {
    setCookie('_mp_src', 'tiktok', 60*60);
  }
})();
</script>
