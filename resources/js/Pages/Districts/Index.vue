<template>
  <AdminLayout title="الأقضية">
    <div class="max-w-lg">
      <!-- Add form -->
      <div class="card mb-4">
        <form @submit.prevent="addDistrict" class="flex gap-3">
          <input v-model="newName" type="text" placeholder="اسم القضاء الجديد..." class="form-input flex-1" required />
          <button type="submit" :disabled="form.processing" class="btn-primary">
            + إضافة
          </button>
        </form>
      </div>

      <!-- List Table -->
      <div class="card overflow-hidden p-0">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>اسم القضاء</th>
              <th>عدد الطلبات</th>
              <th>الحالة</th>
              <th>الإجراء</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(d, i) in districts" :key="d.id">
              <td class="text-slate-400 text-xs font-mono text-center">{{ i + 1 }}</td>
              <td class="font-semibold text-slate-800">{{ d.name }}</td>
              <td>
                <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                  {{ d.orders_count }} طلب
                </span>
              </td>
              <td>
                <button @click="toggleActive(d)"
                  class="badge cursor-pointer"
                  :class="d.is_active ? 'badge-delivered' : 'badge-pending'">
                  {{ d.is_active ? 'نشط' : 'موقوف' }}
                </button>
              </td>
              <td>
                <button @click="askDelete(d)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium
                         bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition-all">
                  🗑 حذف
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="!districts.length" class="text-slate-400 text-sm text-center py-6">لا توجد أقضية</p>
      </div>
    </div>

    <!-- Confirm Delete -->
    <ConfirmModal
      :show="!!deleteTarget"
      title="حذف القضاء"
      :message="`هل تريد حذف قضاء &quot;${deleteTarget?.name ?? ''}&quot;؟`"
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

const props = defineProps({ districts: Array });
const newName      = ref('');
const deleteTarget = ref(null);
const form = useForm({ name: '' });

function addDistrict() {
  form.name = newName.value;
  form.post(route('admin.districts.store'), { onSuccess: () => { newName.value = ''; } });
}
function toggleActive(d) {
  router.patch(route('admin.districts.toggle-active', d.id), {}, { preserveScroll: true });
}
function askDelete(d) { deleteTarget.value = d; }
function doDelete() {
  router.delete(route('admin.districts.destroy', deleteTarget.value.id), {
    onFinish: () => { deleteTarget.value = null; }
  });
}
</script>
