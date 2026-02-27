<template>
  <AdminLayout title="المحافظات">
    <div class="max-w-lg">
      <!-- Add form -->
      <div class="card mb-4">
        <form @submit.prevent="addGovernorate" class="flex gap-3">
          <input v-model="form.name" type="text" placeholder="اسم المحافظة الجديدة..." class="form-input flex-1" required />
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
              <th>اسم المحافظة</th>
              <th>عدد الأقضية</th>
              <th>الحالة</th>
              <th>الإجراء</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(g, i) in governorates" :key="g.id">
              <td class="text-slate-400 text-xs font-mono text-center">{{ i + 1 }}</td>
              <td class="font-semibold text-slate-800">{{ g.name }}</td>
              <td>
                <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                  {{ g.districts_count }} قضاء
                </span>
              </td>
              <td>
                <button @click="toggleActive(g)"
                  class="badge cursor-pointer"
                  :class="g.is_active ? 'badge-delivered' : 'badge-pending'">
                  {{ g.is_active ? 'نشط' : 'موقوف' }}
                </button>
              </td>
              <td>
                <button @click="askDelete(g)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium
                         bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition-all">
                  🗑 حذف
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="!governorates.length" class="text-slate-400 text-sm text-center py-6">لا توجد محافظات</p>
      </div>
    </div>

    <!-- Confirm Delete -->
    <ConfirmModal
      :show="!!deleteTarget"
      title="حذف المحافظة"
      :message="`هل تريد حذف محافظة &quot;${deleteTarget?.name ?? ''}&quot;؟`"
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

const props = defineProps({ governorates: Array });
const deleteTarget = ref(null);
const form = useForm({ name: '' });

function addGovernorate() {
  form.post(route('admin.governorates.store'), { onSuccess: () => { form.reset(); } });
}
function toggleActive(g) {
  router.patch(route('admin.governorates.toggle-active', g.id), {}, { preserveScroll: true });
}
function askDelete(g) { deleteTarget.value = g; }
function doDelete() {
  router.delete(route('admin.governorates.destroy', deleteTarget.value.id), {
    onFinish: () => { deleteTarget.value = null; }
  });
}
</script>
