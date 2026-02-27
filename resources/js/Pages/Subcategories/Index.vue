<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
  category: Object,
  subcategories: Object,
  filters: Object,
});

const search = ref(props.filters?.search ?? '');
let searchTimeout = null;

function onSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('admin.categories.subcategories.index', props.category.id), { search: search.value }, { preserveState: true });
  }, 300);
}

// ── Modals State ──
const isModalOpen  = ref(false);
const isEditMode   = ref(false);
const isConfirmOpen = ref(false);
const itemToDelete  = ref(null);

const form = useForm({
  id: null,
  name: '',
  image: null,
});

const imagePreview = ref(null);

function handleImageUpload(e) {
  const file = e.target.files[0];
  if (file) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
}

function openAddModal() {
  isEditMode.value = false;
  form.reset();
  form.clearErrors();
  imagePreview.value = null;
  isModalOpen.value = true;
}

function openEditModal(sub) {
  isEditMode.value = true;
  form.reset();
  form.clearErrors();
  form.id    = sub.id;
  form.name  = sub.name;
  form.image = null; 
  imagePreview.value = sub.image_url;
  isModalOpen.value = true;
}

function submitForm() {
  if (isEditMode.value) {
    form.post(route('admin.categories.subcategories.update', { category: props.category.id, subcategory: form.id }), {
      forceFormData: true,
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
      headers: {
        'X-HTTP-Method-Override': 'PUT',
      },
    });
  } else {
    form.post(route('admin.categories.subcategories.store', props.category.id), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      }
    });
  }
}

function confirmDelete(sub) {
  itemToDelete.value = sub;
  isConfirmOpen.value = true;
}

function deleteItem() {
  if (itemToDelete.value) {
    router.delete(route('admin.categories.subcategories.destroy', { category: props.category.id, subcategory: itemToDelete.value.id }), {
      onSuccess: () => isConfirmOpen.value = false,
    });
  }
}

function toggleActive(sub) {
  router.post(route('admin.categories.subcategories.toggle-active', { category: props.category.id, subcategory: sub.id }), {}, { preserveScroll: true });
}
</script>

<template>
  <AdminLayout title="الأصناف الفرعية">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <button @click="router.visit(route('admin.categories.index'))" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-right"></i>
          </button>
          <h1 class="text-2xl font-bold text-slate-800">الأصناف الفرعية</h1>
        </div>
        <p class="text-slate-500 text-sm">إدارة الأصناف التابعة لقسم: <span class="font-bold text-primary">{{ category.name }}</span></p>
      </div>
      <button @click="openAddModal" class="btn-primary w-full sm:w-auto">
        <i class="fas fa-plus mr-2"></i> إضافة صنف فرعي
      </button>
    </div>

    <div class="card p-0 mb-6">
      <div class="p-4 border-b border-slate-100">
        <div class="relative max-w-md">
          <input 
            v-model="search" 
            @input="onSearch"
            type="text" 
            class="form-input pl-10" 
            placeholder="ابحث عن صنف فرعي..."
          >
          <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-right">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-sm border-b border-slate-100">
              <th class="p-4 font-semibold">الصورة</th>
              <th class="p-4 font-semibold">اسم الصنف</th>
              <th class="p-4 font-semibold">عدد المنتجات</th>
              <th class="p-4 font-semibold">الحالة</th>
              <th class="p-4 font-semibold text-center">الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sub in subcategories.data" :key="sub.id" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
              <td class="p-4">
                <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center">
                  <img v-if="sub.image_url" :src="sub.image_url" class="w-full h-full object-cover" alt="Image">
                  <i v-else class="fas fa-image text-slate-300"></i>
                </div>
              </td>
              <td class="p-4 font-medium text-slate-800">{{ sub.name }}</td>
              <td class="p-4 text-slate-600">
                <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md text-xs font-semibold">
                  {{ sub.products_count }} منتج
                </span>
              </td>
              <td class="p-4">
                <button @click="toggleActive(sub)" class="focus:outline-none">
                  <StatusBadge :status="sub.is_active" activeText="نشط" inactiveText="مخفي" />
                </button>
              </td>
              <td class="p-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="openEditModal(sub)" class="w-8 h-8 rounded-lg text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors" title="تعديل">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="confirmDelete(sub)" class="w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors" title="حذف">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="subcategories.data.length === 0">
              <td colspan="5" class="p-8 text-center text-slate-500">
                <div class="flex flex-col items-center justify-center">
                  <i class="fas fa-folder-open text-4xl text-slate-300 mb-3"></i>
                  <p>لا توجد أصناف فرعية، ابدأ بإضافة صنف جديد.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="subcategories.links && subcategories.links.length > 3" class="p-4 border-t border-slate-100 flex items-center justify-center gap-1">
        <template v-for="(link, i) in subcategories.links" :key="i">
          <button 
            @click="link.url ? router.get(link.url) : null"
            v-html="link.label"
            class="px-3 py-1 text-sm rounded-md border transition-colors"
            :class="[
              link.active ? 'bg-primary border-primary text-white' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50',
              !link.url ? 'opacity-50 cursor-not-allowed' : ''
            ]"
            :disabled="!link.url"
          ></button>
        </template>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Modal :show="isModalOpen" :title="isEditMode ? 'تعديل الصنف الفرعي' : 'إضافة صنف فرعي جديد'" @close="isModalOpen = false">
      <form @submit.prevent="submitForm">
        <div class="space-y-4">
          <div class="form-group">
            <label class="form-label">اسم الصنف الفرعي *</label>
            <input v-model="form.name" type="text" class="form-input" required placeholder="مثال: مياه غازية">
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div class="form-group">
            <label class="form-label">صورة الصنف <span v-if="!isEditMode">*</span></label>
            <div class="mt-1 flex items-center gap-4">
              <div class="w-20 h-20 rounded-xl border border-slate-200 overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover">
                <i v-else class="fas fa-image text-slate-300 text-2xl"></i>
              </div>
              <div class="flex-1">
                <input type="file" @change="handleImageUpload" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-colors">
                <p class="text-xs text-slate-400 mt-2">صيغ مدعومة: JPG, PNG, WEBP. حجم أقصى: 2MB</p>
              </div>
            </div>
            <p v-if="form.errors.image" class="text-red-500 text-xs mt-1">{{ form.errors.image }}</p>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3 ext-action-buttons">
          <button type="button" @click="isModalOpen = false" class="btn-light">إلغاء</button>
          <button type="submit" class="btn-primary" :disabled="form.processing">
            <i class="fas fa-spinner fa-spin mr-2" v-if="form.processing"></i>
            حفظ
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal 
      :show="isConfirmOpen" 
      title="حذف الصنف الفرعي"
      message="هل أنت متأكد من رغبتك في حذف هذا الصنف النهائي؟ لا يمكن التراجع عن هذا الإجراء."
      confirmText="نعم، احذف الصنف"
      @confirm="deleteItem"
      @close="isConfirmOpen = false"
    />
  </AdminLayout>
</template>
