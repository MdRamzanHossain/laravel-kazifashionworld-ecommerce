<div 
    wire:ignore
    x-data="{
        show: false,
        isDismissed: false,

        init() {
            if (sessionStorage.getItem('kazi_fb_toast_dismissed') === '1') {
                return;
            }
            
            setTimeout(() => {
                if (!this.isDismissed) {
                    this.show = true;
                    
                    // Auto-close after 3 seconds
                    setTimeout(() => {
                        if (!this.isDismissed) {
                            this.show = false;
                        }
                    }, 3000);
                }
            }, 3000);
        },

        dismiss() {
            this.show = false;
            this.isDismissed = true;
            sessionStorage.setItem('kazi_fb_toast_dismissed', '1');
        },

        openFacebookApp() {
            // Attempt to open native app via deep link
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            const fbAppUrl = isIOS 
                ? 'fb://profile/100063704257134' // iOS deep link (requires Page ID, using generic fallback if unknown)
                : 'intent://page/kajifashion#Intent;package=com.facebook.katana;scheme=fb;end'; // Android intent
            
            const fbWebUrl = 'https://www.facebook.com/kajifashion';

            // Try opening the app
            window.location.href = isIOS ? fbAppUrl : 'intent://facewebmodal/f?href=' + fbWebUrl + '#Intent;package=com.facebook.katana;scheme=fb;end';

            // Fallback to web if app fails to open after 1.5 seconds
            setTimeout(() => {
                window.open(fbWebUrl, '_blank');
            }, 1500);
        }
    }"
    class="fixed bottom-4 left-4 sm:bottom-6 sm:left-6 z-40 w-[300px] sm:w-[320px] pointer-events-none select-none"
>
    <div 
        x-show="show" 
        x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="pointer-events-auto bg-white backdrop-blur-xl border border-gray-200/90 shadow-[0_12px_40px_-6px_rgba(0,0,0,0.2)] rounded-2xl p-4 relative overflow-hidden"
        style="display: none;"
    >
        <!-- Background Subtle Ambient Glow -->
        <div class="absolute -right-8 -bottom-8 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Dismiss Button -->
        <button 
            type="button" 
            @click.stop="dismiss()"
            class="absolute top-2 right-2 text-gray-400 hover:text-gray-800 bg-gray-50 w-6 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center text-sm font-bold shadow-sm transition z-10"
            title="Dismiss"
        >
            &times;
        </button>

        <!-- Custom Facebook UI that Deep Links -->
        <div class="flex items-start gap-3 relative z-10">
            <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0 overflow-hidden border border-gray-200">
                <img src="{{ asset('images/logo.png') }}" alt="Kazi Fashion" class="w-full h-full object-contain p-1 bg-black">
            </div>
            <div class="flex-1">
                <h4 class="text-[13px] font-bold text-gray-900 leading-tight">Kazi Fashion World</h4>
                <p class="text-[11px] text-gray-500 mt-0.5">52k+ Followers on Facebook</p>
                
                <button 
                    type="button"
                    @click="openFacebookApp()"
                    class="mt-2.5 inline-flex items-center gap-1.5 bg-[#1877F2] hover:bg-[#166FE5] text-white text-[11px] font-bold py-1.5 px-3 rounded-md transition shadow-sm"
                >
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span>Follow Page</span>
                </button>
            </div>
        </div>
    </div>
</div>
