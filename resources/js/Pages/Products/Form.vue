<template>
  <AdminLayout :title="isEdit ? 'تعديل منتج' : 'إضافة منتج'">
    <div class="max-w-3xl">
      <form @submit.prevent="submit" enctype="multipart/form-data">
        <div class="card space-y-5">

          <!-- Row 1 -->
          <div class="grid grid-cols-2 gap-4">
            <div class="form-group col-span-2">
              <label class="form-label">اسم المنتج *</label>
              <input v-model="form.name" type="text" class="form-input" placeholder="مثال: قارورة ماء 19 لتر" required />
              <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
            </div>

            <div class="form-group">
              <label class="form-label">القسم *</label>
              <select v-model="form.category_id" class="form-select" required>
                <option value="">اختر قسماً</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">الحجم / المقاس</label>
              <input v-model="form.size" type="text" class="form-input" placeholder="مثال: 19 لتر" />
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

const form = useForm({
  category_id:  props.product?.category_id ?? '',
  name:         props.product?.name ?? '',
  description:  props.product?.description ?? '',
  size:         props.product?.size ?? '',
  price:        props.product?.price ?? '',
  sale_price:   props.product?.sale_price ?? '',
  is_on_sale:   props.product?.is_on_sale ?? false,
  cost_price:   props.product?.cost_price ?? '',
  is_active:    props.product?.is_active ?? true,
  is_available: props.product?.is_available ?? true,
  images:       [],
  deleted_images: [],
  _method:      isEdit.value ? 'PUT' : 'POST',
});

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
