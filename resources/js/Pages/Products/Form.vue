<template>
  <AdminLayout :title="isEdit ? 'تعديل منتج' : 'إضافة منتج'">
    <div class="max-w-3xl mx-auto">
      <!-- Back Button -->
      <div class="flex items-center justify-end mb-4">
        <Link :href="route('admin.products.index')" class="flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-primary-700 hover:bg-white transition-all bg-slate-50 px-4 py-2 rounded-xl border border-slate-200 hover:border-primary-200 shadow-sm w-fit">
          <i class="fas fa-arrow-right text-xs"></i>
          العودة للمنتجات
        </Link>
      </div>

      <form @submit.prevent="submit" enctype="multipart/form-data">
        <div class="card space-y-5">

          <!-- Row 1 -->
          <div class="grid grid-cols-2 gap-4">
            <div class="form-group col-span-2 sm:col-span-1">
              <label class="form-label">اسم المنتج *</label>
              <input v-model="form.name" type="text" class="form-input" placeholder="مثال: قارورة ماء 19 لتر" required />
              <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
            </div>

            <div class="form-group col-span-2 sm:col-span-1">
              <label class="form-label">رقم باركود/مسلسل (SKU)</label>
              <input v-model="form.sku" type="text" class="form-input" placeholder="مثال: ABC-123 (اختياري)" />
              <p v-if="form.errors.sku" class="text-red-500 text-xs mt-1">{{ form.errors.sku }}</p>
            </div>

            <div class="form-group col-span-2 sm:col-span-1">
              <label class="form-label">القسم *</label>
              <select v-model="form.category_id" class="form-select" required>
                <option value="">اختر قسماً</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            
            <div class="form-group col-span-2 sm:col-span-1" v-if="availableSubcategories.length > 0">
              <label class="form-label">الصنف الفرعي (اختياري)</label>
              <select v-model="form.subcategory_id" class="form-select">
                <option value="">بدون صنف</option>
                <option v-for="s in availableSubcategories" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
          </div>

          <!-- Description -->
          <div class="form-group">
            <label class="form-label">الوصف *</label>
            <textarea v-model="form.description" class="form-textarea" rows="3" required></textarea>
          </div>

          <!-- Prices -->
          <div class="grid grid-cols-3 gap-4">
            <div class="form-group">
              <label class="form-label">سعر البيع (د.ع) *</label>
              <input v-model="form.price" type="number" min="0" step="0.01" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">سعر التكلفة (د.ع) *</label>
              <input v-model="form.cost_price" type="number" min="0" step="0.01" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">سعر الخصم (د.ع)</label>
              <input v-model="form.sale_price" type="number" min="0" step="0.01" class="form-input" />
            </div>
          </div>

          <!-- Toggles -->
          <div class="flex flex-wrap gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.is_on_sale" class="rounded text-primary-600" />
              <span class="text-sm font-medium text-slate-700">تفعيل سعر الخصم</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.is_active" class="rounded text-primary-600" />
              <span class="text-sm font-medium text-slate-700">ظاهر في التطبيق</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.is_available" class="rounded text-primary-600" />
              <span class="text-sm font-medium text-slate-700">متوفر حالياً</span>
            </label>
          </div>

          <!-- Variants / Stock -->
          <div class="col-span-full border border-slate-200 rounded-xl p-4 bg-slate-50 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-bold text-slate-800 text-sm">الخيارات والمخزون</h3>
                <p class="text-xs text-slate-500 mt-0.5">أضف خيارات المنتج مثل الألوان والأحجام وكمية كل منها. إذا كان المنتج عاماً، أضف مخزونه فقط.</p>
              </div>
              <button type="button" @click="addVariant" class="btn-secondary text-xs px-3 py-1.5 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                خيار جديد
              </button>
            </div>
            
            <div v-for="(v, index) in form.variants" :key="index" class="flex flex-wrap items-end gap-3 bg-white p-3 rounded-lg border border-slate-200 relative">
              <button v-if="form.variants.length > 1" type="button" @click="removeVariant(index)" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600 transition shadow-sm pb-0.5">×</button>
              
              <div class="form-group mb-0 flex-1 min-w-[120px]">
                <label class="form-label text-xs mb-1">اللون (اختياري)</label>
                <input v-model="v.color" type="text" class="form-input text-sm py-1.5 px-2" placeholder="أحمر، أسود، إلخ" />
              </div>
              
              <div class="form-group mb-0 flex-1 min-w-[120px]">
                <label class="form-label text-xs mb-1">القياس (اختياري)</label>
                <input v-model="v.size" type="text" class="form-input text-sm py-1.5 px-2" placeholder="M, 42, XXL" />
              </div>
              
              <div class="form-group mb-0 flex-1 min-w-[120px]">
                <label class="form-label text-xs mb-1 text-primary-700 font-bold">الكمية المتوفرة *</label>
                <input v-model="v.stock" type="number" min="0" class="form-input text-sm py-1.5 px-2 border-primary-200 focus:border-primary-500 focus:ring-primary-500 bg-primary-50/50 font-bold text-primary-900" required />
              </div>
            </div>
            <p v-if="form.errors.variants" class="text-red-500 text-xs mt-1">{{ form.errors.variants }}</p>
          </div>

          <!-- Image Upload (up to 5) -->
          <div class="form-group">
            <label class="form-label">الصور (حتى 5 صور)</label>

            <!-- Existing Images (on edit) -->
            <div v-if="existingImages.length" class="flex flex-wrap gap-3 mb-3">
              <div v-for="img in existingImages" :key="img.id" class="relative group">
                <img :src="img.url" class="w-20 h-20 rounded-xl object-cover border border-slate-200" />
                <button type="button" @click="removeExistingImage(img.id)"
                  class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-xs
                         hidden group-hover:flex items-center justify-center">
                  ×
                </button>
              </div>
            </div>

            <!-- New Images Preview -->
            <div v-if="previews.length" class="flex flex-wrap gap-3 mb-3">
              <div v-for="(src, i) in previews" :key="i" class="relative group">
                <img :src="src" class="w-20 h-20 rounded-xl object-cover border-2 border-primary-200" />
                <button type="button" @click="removePreview(i)"
                  class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-xs
                         hidden group-hover:flex items-center justify-center">
                  ×
                </button>
              </div>
            </div>

            <!-- Upload Zone -->
            <div v-if="(existingImages.length + previews.length) < 5"
              class="image-drop-zone" @click="$refs.imgInput.click()">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <p class="text-sm text-slate-500">اضغط لاختيار صور</p>
              <p class="text-xs text-slate-400 mt-1">{{ 5 - existingImages.length - previews.length }} صور متبقية — JPEG/PNG/WEBP حتى 3MB</p>
              <input ref="imgInput" type="file" multiple accept="image/*" class="hidden"
                @change="onImages" :max="5 - existingImages.length" />
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-3 mt-5">
          <button type="submit" :disabled="form.processing" class="btn-primary">
            <svg v-if="form.processing" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ isEdit ? 'حفظ التعديلات' : 'إضافة المنتج' }}
          </button>
          <Link :href="route('admin.products.index')" class="btn-secondary">إلغاء</Link>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ product: Object, categories: Array });

const isEdit        = computed(() => !!props.product);
const existingImages = ref(props.product?.images ?? []);
const deletedImages  = ref([]);
const previews       = ref([]);
const newFiles       = ref([]);

const availableSubcategories = computed(() => {
  if (!form?.category_id) return [];
  const cat = props.categories.find(c => c.id === form.category_id);
  return cat?.subcategories || [];
});

const form = useForm({
  category_id:  props.product?.category_id ?? '',
  subcategory_id: props.product?.subcategory_id ?? null,
  name:         props.product?.name ?? '',
  sku:          props.product?.sku ?? '',
  description:  props.product?.description ?? '',
  price:        props.product?.price ?? '',
  sale_price:   props.product?.sale_price ?? '',
  is_on_sale:   props.product?.is_on_sale ?? false,
  cost_price:   props.product?.cost_price ?? '',
  is_active:    props.product?.is_active ?? true,
  is_available: props.product?.is_available ?? true,
  variants:     props.product?.variants?.length 
                ? props.product.variants 
                : [{ id: null, color: '', size: '', stock: 0 }],
  images:       [],
  deleted_images: [],
  _method:      isEdit.value ? 'PUT' : 'POST',
});

function addVariant() {
  form.variants.push({ id: null, color: '', size: '', stock: 0 });
}

function removeVariant(index) {
  if (form.variants.length > 1) {
    if (form.variants[index].id) {
      // Opt: if you want to track deleted specific variants, we do it backend by sync.
    }
    form.variants.splice(index, 1);
  }
}

function onImages(e) {
  const files = Array.from(e.target.files);
  const allowed = 5 - existingImages.value.length - previews.value.length;
  files.slice(0, allowed).forEach(f => {
    newFiles.value.push(f);
    previews.value.push(URL.createObjectURL(f));
  });
  e.target.value = '';
}

function removePreview(i) {
  previews.value.splice(i, 1);
  newFiles.value.splice(i, 1);
}

function removeExistingImage(id) {
  deletedImages.value.push(id);
  existingImages.value = existingImages.value.filter(img => img.id !== id);
}

function submit() {
  form.images         = newFiles.value;
  form.deleted_images = deletedImages.value;

  const url = isEdit.value
    ? route('admin.products.update', props.product.id)
    : route('admin.products.store');

  form.post(url);
}
</script>
