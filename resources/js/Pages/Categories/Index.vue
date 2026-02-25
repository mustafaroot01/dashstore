<template>
  <AdminLayout title="الأقسام">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <input v-model="search" type="text" placeholder="بحث..." class="form-input w-56" @input="doSearch" />
        <span class="text-slate-400 text-sm">{{ categories.total }} قسم</span>
      </div>
      <button @click="openAdd" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        إضافة قسم
      </button>
    </div>

    <!-- List Table -->
    <div class="card overflow-hidden p-0">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:50px">#</th>
            <th>الصورة</th>
            <th>اسم القسم</th>
            <th>عدد المنتجات</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(cat, i) in categories.data" :key="cat.id">
            <td class="text-slate-400 text-xs font-mono text-center">
              {{ (categories.current_page - 1) * categories.per_page + i + 1 }}
            </td>
            <td>
              <img :src="'/storage/' + cat.image"
                class="w-12 h-12 rounded-xl object-cover border border-slate-100" />
            </td>
            <td class="font-semibold text-slate-800">{{ cat.name }}</td>
            <td>
              <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                {{ cat.products_count }} منتج
              </span>
            </td>
            <td>
              <button @click="toggleActive(cat)"
                class="badge cursor-pointer"
                :class="cat.is_active ? 'badge-delivered' : 'badge-pending'">
                {{ cat.is_active ? 'نشط' : 'مخفي' }}
              </button>
            </td>
            <td>
              <div class="flex items-center gap-2">
                <button @click="openEdit(cat)" class="btn-secondary py-1.5 px-3 text-xs">
                  ✏️ تعديل
                </button>
                <button @click="askDelete(cat)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium
                         bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition-all">
                  🗑 حذف
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="!categories.data.length" class="text-slate-400 text-sm text-center py-8">لا توجد أقسام</p>
    </div>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
        <div class="modal-box max-w-md">
          <div class="flex items-center justify-between p-5 border-b">
            <h3 class="font-bold text-slate-800">{{ editItem ? 'تعديل قسم' : 'قسم جديد' }}</h3>
            <button @click="closeModal" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
          </div>
          <form @submit.prevent="submitForm" class="p-5 space-y-4">
            <div class="form-group">
              <label class="form-label">اسم القسم *</label>
              <input v-model="form.name" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">{{ editItem ? 'صورة جديدة (اختياري)' : 'الصورة *' }}</label>
              <div class="image-drop-zone" @click="$refs.catImg.click()">
                <img v-if="preview" :src="preview" class="w-28 h-28 object-cover rounded-xl mx-auto" />
                <div v-else class="py-4 text-slate-400 text-sm">اضغط لاختيار صورة</div>
                <input ref="catImg" type="file" accept="image/*" class="hidden" @change="onImg" />
              </div>
            </div>
            <div class="flex gap-3 pt-1">
              <button type="submit" :disabled="form.processing" class="btn-primary flex-1">
                {{ editItem ? 'حفظ التعديلات' : 'إضافة' }}
              </button>
              <button type="button" @click="closeModal" class="btn-secondary">إلغاء</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Confirm Delete -->
    <ConfirmModal
      :show="!!deleteTarget"
      :title="`حذف القسم`"
      :message="`هل تريد حذف قسم &quot;${deleteTarget?.name ?? ''}&quot;؟ سيتم حذف صورته أيضاً.`"
      confirm-label="حذف"
      @confirm="doDelete"
      @cancel="deleteTarget = null"
    />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout   from '@/Layouts/AdminLayout.vue';
import ConfirmModal  from '@/Components/ConfirmModal.vue';

const props = defineProps({ categories: Object, filters: Object });

const search      = ref(props.filters?.search ?? '');
const showModal   = ref(false);
const editItem    = ref(null);
const preview     = ref(null);
const deleteTarget = ref(null);

const form = useForm({ name: '', image: null, _method: 'POST' });

let timer;
function doSearch() {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(route('admin.categories.index'), { search: search.value }, { preserveState: true });
  }, 400);
}

function onImg(e) {
  form.image = e.target.files[0];
  preview.value = URL.createObjectURL(form.image);
}

function openAdd() {
  editItem.value = null; form.reset(); preview.value = null; showModal.value = true;
}

function openEdit(cat) {
  editItem.value = cat; form.name = cat.name; form.image = null;
  preview.value = '/storage/' + cat.image; showModal.value = true;
}

function closeModal() {
  showModal.value = false; editItem.value = null; form.reset(); preview.value = null;
}

function submitForm() {
  const url = editItem.value
    ? route('admin.categories.update', editItem.value.id)
    : route('admin.categories.store');
  form._method = editItem.value ? 'PUT' : 'POST';
  form.post(url, { onSuccess: closeModal });
}

function toggleActive(cat) {
  router.patch(route('admin.categories.toggle-active', cat.id), {}, { preserveScroll: true });
}

function askDelete(cat)  { deleteTarget.value = cat; }
function doDelete()      {
  router.delete(route('admin.categories.destroy', deleteTarget.value.id), {
    onFinish: () => { deleteTarget.value = null; }
  });
}
</script>
