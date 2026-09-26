<div 
    x-data="{
        quickViewOpen: false,
        activeProduct: null,
        activeImageIndex: 0,
        quickQty: 1,
        isAddingToCart: false,
        activeVariation: null,
        variationError: false,
        selectedAttributes: {},
        touchStartX: 0,
        touchEndX: 0,
        hasValidAttributes() {
            if (!this.activeProduct || !this.activeProduct.attributes_schema) return false;
            let attrs = Array.isArray(this.activeProduct.attributes_schema) ? this.activeProduct.attributes_schema : Object.values(this.activeProduct.attributes_schema);
            return attrs.some(a => a && a.name && (a.options || a.values));
        },
        isAllSelected() {
            if (!this.activeProduct || !this.activeProduct.has_variations) return true;
            if (this.activeProduct.attributes_schema && this.hasValidAttributes()) {
                let all = true;
                Object.values(this.activeProduct.attributes_schema).forEach(attr => {
                    if (!this.selectedAttributes[attr.name]) all = false;
                });
                return all;
            }
            return !!this.activeVariation;
        },
        openQuickView(productObj) {
            this.activeProduct = JSON.parse(JSON.stringify(productObj));
            this.activeImageIndex = 0;
            this.quickQty = 1;
            this.isAddingToCart = false;
            this.activeVariation = null;
            this.variationError = false;
            this.selectedAttributes = {};
            this.quickViewOpen = true;
            document.body.style.overflow = 'hidden';
            
            // Auto-select first variation if only one exists
            if (this.activeProduct.has_variations && (!this.activeProduct.attributes_schema || !this.hasValidAttributes())) {
                if (this.activeProduct.variations && this.activeProduct.variations.length === 1) {
                    this.activeVariation = this.activeProduct.variations[0];
                    this.activeProduct.effective_price = this.activeVariation.sale_price || this.activeVariation.price || this.activeProduct.effective_price;
                    this.activeProduct.price = this.activeVariation.price || this.activeProduct.price;
                }
            }
        },
        selectVariation(variationId) {
            let variation = this.activeProduct.variations.find(v => v.id === variationId);
            if (variation) {
                this.activeVariation = variation;
                this.variationError = false;
            }
        },
        selectAttribute(attrName, val) {
            this.selectedAttributes[attrName] = val;
            this.variationError = false;

            // Try to match DB variation for price/image override
            let allSelected = true;
            let expectedParts = [];
            
            if (this.activeProduct.attributes_schema && this.hasValidAttributes()) {
                Object.values(this.activeProduct.attributes_schema).forEach(attr => {
                    if (!this.selectedAttributes[attr.name]) {
                        allSelected = false;
                    } else {
                        expectedParts.push(this.selectedAttributes[attr.name]);
                    }
                });
            }

            if (allSelected) {
                let expectedName = expectedParts.join(' - ');
                this.activeVariation = this.activeProduct.variations.find(v => (v.variation_name || v.name) === expectedName);
                
                if (!this.activeVariation) {
                    let partial = this.activeProduct.variations.find(v => expectedParts.includes(v.variation_name || v.name));
                    if (partial) {
                        this.activeVariation = { ...partial, fake_match: true };
                    }
                }
                
                if (this.activeVariation) {
                    this.activeProduct.effective_price = this.activeVariation.sale_price || this.activeVariation.price || this.activeProduct.effective_price;
                    this.activeProduct.price = this.activeVariation.price || this.activeProduct.price;
                }
            } else {
                this.activeVariation = null;
            }
        },
        closeQuickView() {
            this.quickViewOpen = false;
            this.activeProduct = null;
            document.body.style.overflow = '';
        },
        nextImage() {
            if (!this.activeProduct || !this.activeProduct.images || this.activeProduct.images.length <= 1) return;
            this.activeImageIndex = (this.activeImageIndex + 1) % this.activeProduct.images.length;
        },
        prevImage() {
            if (!this.activeProduct || !this.activeProduct.images || this.activeProduct.images.length <= 1) return;
            this.activeImageIndex = (this.activeImageIndex - 1 + this.activeProduct.images.length) % this.activeProduct.images.length;
        },
        increaseQty() {
            if (this.activeProduct && this.activeProduct.stock_quantity !== undefined && this.quickQty >= this.activeProduct.stock_quantity) {
                return;
            }
            this.quickQty++;
        },
        decreaseQty() {
            if (this.quickQty > 1) {
                this.quickQty--;
            }
        },
        async addToBag() {
            if (this.isAddingToCart) return;
            
            let allSelected = true;
            if (this.activeProduct.has_variations && this.activeProduct.attributes_schema && this.hasValidAttributes()) {
                Object.values(this.activeProduct.attributes_schema).forEach(attr => {
                    if (!this.selectedAttributes[attr.name]) allSelected = false;
                });
            } else if (this.activeProduct.has_variations && !this.activeVariation) {
                allSelected = false;
            }

            if (!allSelected) {
                this.variationError = true;
                return;
            }
            this.variationError = false;
            this.isAddingToCart = true;
            
            if (typeof fbq === 'function') {
                fbq('track', 'AddToCart', {
                    content_name: this.activeProduct.name,
                    content_ids: [this.activeProduct.id],
                    content_type: 'product',
                    value: this.activeProduct.sale_price ? this.activeProduct.sale_price : this.activeProduct.price,
                    currency: 'BDT'
                });
            }
            
            let attributesString = '';
            if (this.activeProduct.attributes_schema && this.hasValidAttributes()) {
                let parts = [];
                for (let key in this.selectedAttributes) {
                    parts.push(key + ': ' + this.selectedAttributes[key]);
                }
                attributesString = parts.join(', ');
            } else if (this.activeVariation) {
                attributesString = this.activeVariation.variation_name || this.activeVariation.name;
            }

            await $wire.addToCart(this.activeProduct.id, this.quickQty, (this.activeVariation && !this.activeVariation.fake_match) ? this.activeVariation.id : null, attributesString);
            
            this.isAddingToCart = false;
            this.closeQuickView();
        }
    }"
    @open-quick-view.window="openQuickView($event.detail)" @close-quick-view.window="closeQuickView()"
>
    <!-- LUXURY QUICK SHORT DETAILS VIEW POPUP MODAL -->
    <template x-teleport="body">
        <div 
            x-show="quickViewOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 backdrop-blur-none"
            x-transition:enter-end="opacity-100 backdrop-blur-md"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 backdrop-blur-md"
            x-transition:leave-end="opacity-0 backdrop-blur-none"
            class="fixed inset-0 z-[99999] bg-black/60 flex items-center justify-center p-4 sm:p-6"
            style="display: none;"
            @click.self="closeQuickView()"
        >
            <div 
                x-show="quickViewOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-8"
                class="bg-white rounded-[32px] max-w-5xl w-full max-h-[90vh] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.4)] relative flex flex-col md:flex-row overflow-hidden ring-1 ring-black/5"
                @click.stop
            >
                <!-- Close Button (Floating Top-Right) -->
                <button 
                    type="button" 
                    @click="closeQuickView()"
                    class="absolute top-4 right-4 md:top-5 md:right-5 w-10 h-10 rounded-full bg-white/90 hover:bg-white backdrop-blur shadow-md text-gray-800 hover:text-black flex items-center justify-center text-xl font-bold transition z-50 cursor-pointer"
                    title="Close"
                >
                    &times;
                </button>

                <template x-if="activeProduct">
                    <div class="flex flex-col md:flex-row w-full h-full max-h-[90vh]">
                        
                        <!-- Left: Interactive Image Gallery -->
                        <div class="w-full md:w-1/2 bg-gray-50 flex flex-col relative group/gallery border-b md:border-b-0 md:border-r border-gray-100 min-h-[300px] md:min-h-[500px]">
                            <!-- Main Slide Viewport -->
                            <div class="relative w-full h-full flex-1 overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center select-none"
                                @touchstart="touchStartX = $event.changedTouches[0].screenX"
                                @touchend="touchEndX = $event.changedTouches[0].screenX; if (touchStartX - touchEndX > 50) nextImage(); if (touchEndX - touchStartX > 50) prevImage();"
                            >
                                <template x-if="activeProduct.images && activeProduct.images.length > 0">
                                    <img 
                                        :src="activeProduct.images[activeImageIndex]" 
                                        :alt="`${activeProduct.name} - image ${activeImageIndex + 1}`" 
                                        class="w-full h-full object-cover transition-all duration-500 ease-in-out"
                                    >
                                </template>
                                <template x-if="!activeProduct.images || activeProduct.images.length === 0">
                                    <template x-if="activeProduct.image">
                                        <img :src="activeProduct.image" :alt="activeProduct.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!activeProduct.image">
                                        <span class="text-gray-300 text-sm font-medium">No Image Preview</span>
                                    </template>
                                </template>
                            </div>

                            <!-- Hover Navigation Arrows -->
                            <template x-if="activeProduct.images && activeProduct.images.length > 1">
                                <div class="absolute inset-0 flex items-center justify-between p-4 opacity-0 md:group-hover/gallery:opacity-100 transition-opacity duration-300 pointer-events-none z-10 hidden md:flex">
                                    <button @click.stop="prevImage()" class="w-10 h-10 rounded-full bg-white/90 backdrop-blur shadow-lg border border-gray-100 flex items-center justify-center text-gray-800 hover:scale-110 transition pointer-events-auto cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <button @click.stop="nextImage()" class="w-10 h-10 rounded-full bg-white/90 backdrop-blur shadow-lg border border-gray-100 flex items-center justify-center text-gray-800 hover:scale-110 transition pointer-events-auto cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </template>

                            <!-- Bottom Thumbnails (Floating Glassmorphism) -->
                            <template x-if="activeProduct.images && activeProduct.images.length > 1">
                                <div class="absolute bottom-4 left-0 right-0 flex justify-center px-4 z-10">
                                    <div class="flex gap-2 overflow-x-auto p-1.5 bg-white/70 backdrop-blur-md rounded-2xl shadow-md border border-white/60 max-w-full scrollbar-hide snap-x">
                                        <template x-for="(img, idx) in activeProduct.images" :key="idx">
                                            <button 
                                                type="button"
                                                @click="activeImageIndex = idx; activeVariation = null; activeProduct.active_image = img;"
                                                class="w-10 h-12 aspect-[4/5] rounded-xl overflow-hidden transition shrink-0 bg-gray-100 focus:outline-none cursor-pointer relative snap-center"
                                                :class="activeImageIndex === idx ? 'ring-2 ring-gray-900 border-2 border-white scale-105' : 'opacity-70 hover:opacity-100 hover:scale-105 border border-transparent'"
                                            >
                                                <img :src="img" :alt="`Thumbnail ${idx + 1}`" class="w-full h-full object-cover">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Flash Sale Badge (Overlay) -->
                            <template x-if="activeProduct.is_flash_sale && activeProduct.discount_pct > 0">
                                <div class="absolute top-5 left-5 bg-gradient-to-r from-red-600 to-pink-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg z-10 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path></svg>
                                    <span x-text="`${activeProduct.discount_pct}% OFF`"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Right: Product Overview & Actions -->
                        <div class="w-full md:w-1/2 flex flex-col p-6 md:p-8 space-y-5 bg-white overflow-y-auto">
                            
                            <!-- Header Info -->
                            <div>
                                <template x-if="activeProduct.category_name">
                                    <p class="text-xs font-bold tracking-wider text-pink-600 uppercase mb-1.5" x-text="activeProduct.category_name"></p>
                                </template>
                                <h2 class="text-xl md:text-2xl font-black text-gray-900 leading-tight mb-2" x-text="activeProduct.name"></h2>
                                
                                <div class="flex items-center gap-3">
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl font-bold text-gray-900" x-text="`BDT ${new Intl.NumberFormat('en-IN').format(activeProduct.effective_price)}`"></span>
                                            <template x-if="activeProduct.effective_price < activeProduct.price">
                                                <span class="text-sm text-gray-400 line-through font-medium" x-text="`BDT ${new Intl.NumberFormat('en-IN').format(activeProduct.price)}`"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Short Description -->
                            <template x-if="activeProduct.short_description">
                                <p class="text-sm text-gray-500 leading-relaxed line-clamp-3" x-text="activeProduct.short_description"></p>
                            </template>

                            <!-- Variations / Swatches -->
                            <template x-if="activeProduct.has_variations && activeProduct.attributes_schema && hasValidAttributes()">
                                <div class="space-y-5 py-5 border-t border-b border-gray-100">
                                    <template x-for="attribute in activeProduct.attributes_schema" :key="attribute.name">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900 mb-2.5" x-text="attribute.name + ':'"></h3>
                                            
                                            <!-- Swatches (Premium Visual) -->
                                            <template x-if="attribute.type === 'color' && attribute.options">
                                                <div class="flex flex-wrap gap-2.5">
                                                    <template x-for="(option, idx) in attribute.options" :key="idx">
                                                        <button 
                                                            type="button" 
                                                            @click="selectAttribute(attribute.name, option.label); if(option.image) { activeVariation = null; activeProduct.active_image = option.image; activeImageIndex = -1; activeProduct.images = [option.image, ...(activeProduct.images || []).filter(img => img !== option.image)]; activeImageIndex = 0; } if(option.price) { activeVariation = null; activeProduct.effective_price = option.price; activeProduct.price = option.price; }"
                                                            class="relative w-9 h-9 rounded-full border-2 transition-all duration-200 cursor-pointer focus:outline-none flex items-center justify-center group/swatch"
                                                            :class="selectedAttributes[attribute.name] === option.label ? 'border-gray-900 shadow-md scale-110' : 'border-transparent hover:border-gray-300'"
                                                            :title="option.label"
                                                        >
                                                            <span class="w-7 h-7 rounded-full shadow-inner block" :style="`background-color: ${option.color_code || '#ddd'};`"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                            
                                            <!-- Buttons (Standard Text) -->
                                            <template x-if="attribute.type !== 'color' && (attribute.options || attribute.values)">
                                                <div class="flex flex-wrap gap-2">
                                                    <template x-for="val in (attribute.options ? Object.values(attribute.options).map(o => o.label) : attribute.values)" :key="val">
                                                        <button 
                                                            type="button" 
                                                            @click="selectAttribute(attribute.name, val)"
                                                            :class="selectedAttributes[attribute.name] === val ? 'bg-gray-900 border-gray-900 text-white shadow-md' : 'bg-white text-gray-900 hover:bg-gray-50 border-gray-200'"
                                                            class="min-w-[48px] px-4 py-2 border rounded-xl text-sm font-semibold transition-all cursor-pointer"
                                                            x-text="val"
                                                        ></button>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="variationError">
                                        <p class="text-xs text-red-500 font-bold mt-2">Please select all options to continue.</p>
                                    </template>
                                </div>
                            </template>
                            
                            <!-- Legacy Variations Fallback -->
                            <template x-if="activeProduct.has_variations && (!activeProduct.attributes_schema || !hasValidAttributes())">
                                <div class="space-y-3 py-4 border-t border-b border-gray-100">
                                    <h3 class="text-sm font-semibold text-gray-900">Select Option:</h3>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="variation in activeProduct.variations" :key="variation.id">
                                            <button 
                                                type="button"
                                                @click="activeVariation = variation; activeProduct.effective_price = variation.sale_price || variation.price || activeProduct.effective_price; activeProduct.price = variation.price || activeProduct.price;"
                                                :class="activeVariation?.id === variation.id ? 'bg-[#ffeb99] border-[#ffeb99] text-gray-900 shadow-sm' : 'bg-white text-gray-900 border-gray-200 hover:bg-gray-50'"
                                                class="px-4 py-2 border rounded-xl text-sm font-semibold transition-all cursor-pointer"
                                                x-text="variation.name || variation.variation_name"
                                            ></button>
                                        </template>
                                    </div>
                                    <template x-if="variationError">
                                        <p class="text-xs text-red-500 font-bold mt-2">Please select an option to continue.</p>
                                    </template>
                                </div>
                            </template>

                            <!-- Add to Cart / Actions (Bottom Pinned) -->
                            <div class="pt-4 mt-auto">
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                    
                                    <!-- Premium Qty Selector -->
                                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-2xl p-1.5 shrink-0 sm:w-auto w-full">
                                        <button 
                                            type="button" 
                                            @click="decreaseQty()" 
                                            class="w-10 h-10 rounded-xl bg-white text-gray-700 hover:bg-gray-100 flex items-center justify-center font-bold text-lg shadow-sm transition cursor-pointer"
                                        >
                                            -
                                        </button>
                                        <span class="w-12 text-center text-sm font-bold text-gray-900" x-text="quickQty"></span>
                                        <button 
                                            type="button" 
                                            @click="increaseQty()" 
                                            class="w-10 h-10 rounded-xl bg-white text-gray-700 hover:bg-gray-100 flex items-center justify-center font-bold text-lg shadow-sm transition cursor-pointer"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <!-- Premium Add to Bag Button -->
                                    <button 
                                        type="button" 
                                        @click="addToBag()"
                                        :disabled="isAddingToCart || (activeProduct.stock_quantity !== undefined && activeProduct.stock_quantity <= 0)"
                                        class="flex-1 py-4 px-6 bg-gray-900 hover:bg-black disabled:bg-gray-200 disabled:text-gray-400 text-white font-bold text-sm rounded-2xl shadow-xl hover:shadow-2xl shadow-gray-900/20 transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer active:scale-95 group"
                                    >
                                        <template x-if="!isAddingToCart">
                                            <div class="flex items-center gap-2">
                                                <svg x-show="!activeProduct.has_variations" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"/>
                                                </svg>
                                                <span x-text="activeProduct.has_variations ? (isAllSelected() ? 'Add to Cart' : 'Select Options') : 'Add to Cart'"></span>
                                            </div>
                                        </template>
                                        <template x-if="isAddingToCart">
                                            <div class="flex items-center gap-2">
                                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                </svg>
                                                <span>Adding...</span>
                                            </div>
                                        </template>
                                    </button>
                                </div>

                                <!-- View Full Details Link -->
                                <a 
                                    :href="activeProduct.url" 
                                    wire:navigate
                                    class="w-full py-4 px-4 bg-transparent hover:bg-gray-50 text-gray-700 font-bold text-sm rounded-2xl border-2 border-gray-200 hover:border-gray-300 transition-all duration-300 flex items-center justify-center gap-2 group cursor-pointer mt-3"
                                >
                                    <span>View Full Product Details</span>
                                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </a>
                            </div>

                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
