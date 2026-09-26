@php
    $metaPixelId = \App\Services\SettingService::getMetaPixelId();
    $ga4MeasurementId = \App\Services\SettingService::getGa4MeasurementId();
@endphp

@if($metaPixelId)
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ $metaPixelId }}');
fbq('track', 'PageView');

// Listen for custom Livewire events
document.addEventListener('track-add-to-cart', function (e) {
    let data = e.detail[0] || e.detail;
    fbq('track', 'AddToCart', {
        content_name: data.product_name,
        content_ids: [data.product_id],
        content_type: 'product',
        value: data.price,
        currency: 'BDT'
    });
});
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
@endif

@if($ga4MeasurementId)
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4MeasurementId }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '{{ $ga4MeasurementId }}');

  document.addEventListener('track-add-to-cart', function (e) {
      let data = e.detail[0] || e.detail;
      gtag('event', 'add_to_cart', {
        currency: "BDT",
        value: data.price,
        items: [
          {
            item_id: data.product_id,
            item_name: data.product_name,
            price: data.price,
            quantity: 1
          }
        ]
      });
  });
</script>
@endif
