<script>
(function(){
  function setCookie(n,v,sec){
    document.cookie = n+'='+encodeURIComponent(v)+'; Max-Age='+sec+'; Path=/; Domain=.dream-v-doma.com.ua';
  }
  function getCookie(n){
    var m=document.cookie.match('(?:^|; )'+n.replace(/([.$?*|{}()\\[\\]\\\\/+^])/g,'\\$1')+'=([^;]*)');
    return m?decodeURIComponent(m[1]):'';
  }
  var q=(location.search||'').toLowerCase();
  var ref=(document.referrer||'').toLowerCase();
  var ua=(navigator.userAgent||'').toLowerCase();

  // 1) перший хіт із TikTok → ставимо мітку
  if (q.indexOf('ttclid=')!==-1 || ref.indexOf('tiktok')!==-1 || ua.indexOf('tiktok')!==-1) {
    setCookie('_mp_src','tiktok', 60*60); // 60 хв
  }

  // 2) продовжуємо життя мітки
  if (getCookie('_mp_src')==='tiktok') setCookie('_mp_src','tiktok', 60*60);
})();
</script>
