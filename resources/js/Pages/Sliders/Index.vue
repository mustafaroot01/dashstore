<template>
  <AdminLayout title="السلايدات">
    <div class="flex justify-between items-center mb-6">
      <p class="text-slate-500 text-sm">{{ sliders.length }} سلايدة</p>
      <button @click="openModal()" class="btn-primary">+ إضافة سلايدة</button>
    </div>

    <!-- Slider Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="(s, i) in sliders" :key="s.id" class="card group p-0 overflow-hidden">
        <!-- Image -->
        <div class="relative">
          <img :src="'/storage/' + s.image" class="w-full h-40 object-cover" />
          <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 to-transparent"></div>
          <!-- Info Overlay -->
          <div class="absolute bottom-2 right-2 left-2">
            <p v-if="s.title" class="text-white text-sm font-medium truncate">{{ s.title }}</p>
            <!-- Link type badge -->
            <span v-if="s.link_type === 'category'" class="text-xs bg-blue-500/80 text-white px-2 py-0.5 rounded-full mt-1 inline-block">
              📂 {{ s.category?.name }}
            </span>
            <span v-else-if="s.link_type === 'product'" class="text-xs bg-violet-500/80 text-white px-2 py-0.5 rounded-full mt-1 inline-block">
              📦 {{ s.product?.name }}
            </span>
            <span v-else-if="s.link_type === 'external'" class="text-xs bg-emerald-500/80 text-white px-2 py-0.5 rounded-full mt-1 inline-block truncate max-w-full block">
              🔗 رابط خارجي
            </span>
          </div>
          <!-- Status -->
          <span class="absolute top-2 left-2 badge" :class="s.is_active ? 'badge-delivered' : 'badge-pending'">
            {{ s.is_active ? 'نشط' : 'مخفي' }}
          </span>
          <!-- Order # -->
          <span class="absolute top-2 right-2 w-6 h-6 bg-black/30 backdrop-blur text-white text-xs rounded-full flex items-center justify-center font-bold">
            {{ i + 1 }}
          </span>
        </div>
        <!-- Actions -->
        <div class="p-3 flex items-center gap-2">
          <button @click="openModal(s)" class="btn-secondary py-1 px-2 text-xs flex-1">✏️ تعديل</button>
          <button @click="toggleActive(s)" class="btn-secondary py-1 px-2 text-xs"
            :class="s.is_active ? 'text-amber-600' : 'text-emerald-600'">
            {{ s.is_active ? 'إخفاء' : 'تفعيل' }}
          </button>
          <button @click="askDelete(s)"
            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium
                   bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition-all">
            🗑
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div v-if="modal" class="modal-backdrop" @click.self="modal = false">
        <div class="modal-box max-w-md">
          <div class="flex items-center justify-between p-5 border-b">
            <h3 class="font-bold">{{ editItem ? 'تعديل سلايدة' : 'سلايدة جديدة' }}</h3>
            <button @click="modal = false" class="text-xl text-slate-400">&times;</button>
          </div>
          <form @submit.prevent="submit" class="p-5 space-y-4">
            <!-- Image Upload -->
            <div class="form-group">
              <label class="form-label">الصورة {{ editItem ? '(اختياري للتغيير)' : '*' }}</label>
              <div class="image-drop-zone" @click="$refs.sImg.click()">
                <img v-if="preview" :src="preview" class="w-full h-32 object-cover rounded-xl" />
                <div v-else class="py-4 text-slate-400 text-sm text-center">اضغط لاختيار صورة السلايدة</div>
                <input ref="sImg" type="file" accept="image/*" class="hidden" @change="onImg" />
              </div>
            </div>

            <!-- Title -->
            <div class="form-group">
              <label class="form-label">العنوان (اختياري)</label>
              <input v-model="form.title" class="form-input" placeholder="عروض الصيف..." />
            </div>

            <!-- Link Type -->
            <div class="form-group">
              <label class="form-label">نوع الرابط</label>
              <div class="grid grid-cols-3 gap-2">
                <label v-for="opt in linkTypes" :key="opt.value"
                  class="flex flex-col items-center gap-1.5 p-3 border-2 rounded-xl cursor-pointer text-center transition-all"
                  :class="form.link_type === opt.value
                    ? 'border-primary-500 bg-primary-50 text-primary-700'
                    : 'border-slate-200 text-slate-500 hover:border-slate-300'">
                  <input type="radio" v-model="form.link_type" :value="opt.value" class="hidden" />
                  <span class="text-lg">{{ opt.icon }}</span>
                  <span class="text-xs font-medium">{{ opt.label }}</span>
                </label>
              </div>
            </div>

            <!-- Category Selector -->
            <div v-if="form.link_type === 'category'" class="form-group">
              <label class="form-label">اختر القسم *</label>
              <select v-model="form.category_id" class="form-select" required>
                <option value="">-- اختر قسماً --</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <!-- Product Selector -->
            <div v-if="form.link_type === 'product'" class="form-group">
              <label class="form-label">اختر المنتج *</label>
              <select v-model="form.product_id" class="form-select" required>
                <option value="">-- اختر منتجاً --</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>

            <!-- External Link (if link_type = external) -->
            <div v-if="form.link_type === 'external'" class="form-group">
              <label class="form-label">الرابط الخارجي *</label>
              <input v-model="form.link" class="form-input" dir="ltr"
                placeholder="https://example.com/..." :required="form.link_type === 'external'" />
            </div>

            <button type="submit" :disabled="form.processing" class="btn-primary w-full">
              {{ editItem ? 'حفظ التعديلات' : 'إضافة السلايدة' }}
            </button>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Confirm Delete -->
    <ConfirmModal
      :show="!!deleteTarget"
      title="حذف السلايدة"
      :message="`هل تريد حذف هذه السلايدة؟ لا يمكن التراجع.`"
      confirm-label="حذف"
      @confirm="doDelete"
      @cancel="deleteTarget = null"
    />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout  from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props    = defineProps({ sliders: Array, categories: Array, products: Array });
const modal    = ref(false);
const editItem = ref(null);
const preview  = ref(null);
const deleteTarget = ref(null);

const linkTypes = [
  { value: null,       icon: '🚫', label: 'بدون رابط' },
  { value: 'category', icon: '📂', label: 'فتح قسم' },
  { value: 'product',  icon: '📦', label: 'فتح منتج' },
  { value: 'external', icon: '🔗', label: 'رابط خارجي' },
];

const form = useForm({
  image:       null,
  title:       '',
  link_type:   null,
  category_id: '',
  product_id:  '',
  link:        '',
});

function openModal(s = null) {
  editItem.value = s;
  form.reset();
  if (s) {
    form.title       = s.title       ?? '';
    form.link_type   = s.link_type   ?? null;
    form.category_id = s.category_id ?? '';
    form.product_id  = s.product_id  ?? '';
    form.link        = s.link        ?? '';
    preview.value    = '/storage/' + s.image;
  } else {
    preview.value = null;
  }
  modal.value = true;
}

function onImg(e) {
  form.image = e.target.files[0];
  preview.value = URL.createObjectURL(form.image);
}

function submit() {
  const url = editItem.value
    ? route('admin.sliders.update', editItem.value.id)
    : route('admin.sliders.store');
  form.post(url, { onSuccess: () => { modal.value = false; } });
}

function toggleActive(s) {
  router.patch(route('admin.sliders.toggle-active', s.id), {}, { preserveScroll: true });
}

function askDelete(s)  { deleteTarget.value = s; }
function doDelete()    {
  router.delete(route('admin.sliders.destroy', deleteTarget.value.id), {
    onFinish: () => { deleteTarget.value = null; }
  });
}
</script>
