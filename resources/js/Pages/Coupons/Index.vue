<template>
  <AdminLayout title="الكوبونات">
    <div class="flex justify-between items-center mb-6">
      <span class="text-slate-500 text-sm">{{ coupons.total }} كوبون</span>
      <button @click="openModal()" class="btn-primary">+ كوبون جديد</button>
    </div>

    <div class="card overflow-hidden p-0">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:50px">#</th>
            <th>الكود</th>
            <th>النوع</th>
            <th>القيمة</th>
            <th>الاستخدام</th>
            <th>الانتهاء</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(c, i) in coupons.data" :key="c.id">
            <td class="text-slate-400 text-xs font-mono text-center">
              {{ (coupons.current_page - 1) * coupons.per_page + i + 1 }}
            </td>
            <td>
              <code class="bg-slate-100 text-primary-700 px-2.5 py-1 rounded-lg text-sm font-mono font-bold">
                {{ c.code }}
              </code>
            </td>
            <td>
              <span class="text-xs font-medium" :class="c.type === 'percent' ? 'text-violet-600' : 'text-emerald-600'">
                {{ c.type === 'percent' ? '% نسبة' : 'مبلغ ثابت' }}
              </span>
            </td>
            <td class="font-bold text-slate-800">
              {{ c.type === 'percent' ? c.value + '%' : Number(c.value).toLocaleString('ar-IQ') + ' د.ع' }}
            </td>
            <td class="text-sm text-slate-600">
              <span class="font-medium text-slate-800">{{ c.used_count }}</span>
              <span class="text-slate-400"> / {{ c.usage_limit ?? '∞' }}</span>
            </td>
            <td class="text-slate-500 text-sm">
              {{ c.expires_at ? new Date(c.expires_at).toLocaleDateString('ar') : '—' }}
            </td>
            <td>
              <button @click="toggleActive(c)" class="badge cursor-pointer"
                :class="c.is_active ? 'badge-delivered' : 'badge-pending'">
                {{ c.is_active ? 'نشط' : 'موقوف' }}
              </button>
            </td>
            <td>
              <div class="flex gap-2">
                <button @click="openModal(c)" class="btn-secondary py-1.5 px-3 text-xs">✏️ تعديل</button>
                <button @click="askDelete(c)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium
                         bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition-all">
                  🗑 حذف
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="!coupons.data.length" class="text-center text-slate-400 text-sm py-8">لا توجد كوبونات</p>
    </div>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div v-if="modal" class="modal-backdrop" @click.self="modal = false">
        <div class="modal-box max-w-md">
          <div class="flex items-center justify-between p-5 border-b">
            <h3 class="font-bold">{{ editItem ? 'تعديل كوبون' : 'كوبون جديد' }}</h3>
            <button @click="modal = false" class="text-xl text-slate-400">&times;</button>
          </div>
          <form @submit.prevent="submit" class="p-5 space-y-4">
            <div class="form-group">
              <label class="form-label">الكود (يُولَّد تلقائياً إن تُرك فارغاً)</label>
              <input v-model="form.code" class="form-input font-mono uppercase" placeholder="AMWAJ10" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="form-group">
                <label class="form-label">النوع</label>
                <select v-model="form.type" class="form-select">
                  <option value="percent">نسبة %</option>
                  <option value="fixed">مبلغ ثابت</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">القيمة</label>
                <input v-model="form.value" type="number" min="0" step="0.01" class="form-input" required />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="form-group">
                <label class="form-label">حد الاستخدام</label>
                <input v-model="form.usage_limit" type="number" min="1" class="form-input" placeholder="∞ غير محدود" />
              </div>
              <div class="form-group">
                <label class="form-label">تاريخ الانتهاء</label>
                <input v-model="form.expires_at" type="date" class="form-input" />
              </div>
            </div>
            <button type="submit" :disabled="form.processing" class="btn-primary w-full">
              {{ editItem ? 'حفظ التعديلات' : 'إنشاء الكوبون' }}
            </button>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Confirm Delete -->
    <ConfirmModal
      :show="!!deleteTarget"
      title="حذف الكوبون"
      :message="`هل تريد حذف كوبون &quot;${deleteTarget?.code ?? ''}&quot;؟`"
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

const props    = defineProps({ coupons: Object });
const modal    = ref(false);
const editItem = ref(null);
const deleteTarget = ref(null);

const form = useForm({ code: '', type: 'percent', value: '', usage_limit: '', expires_at: '' });

function openModal(c = null) {
  editItem.value = c;
  if (c) { form.code = c.code; form.type = c.type; form.value = c.value;
           form.usage_limit = c.usage_limit ?? ''; form.expires_at = c.expires_at?.split('T')[0] ?? ''; }
  else form.reset();
  modal.value = true;
}

function submit() {
  const url    = editItem.value ? route('admin.coupons.update', editItem.value.id) : route('admin.coupons.store');
  const method = editItem.value ? 'patch' : 'post';
  form[method](url, { onSuccess: () => { modal.value = false; } });
}

function toggleActive(c) {
  router.patch(route('admin.coupons.toggle-active', c.id), {}, { preserveScroll: true });
}

function askDelete(c)  { deleteTarget.value = c; }
function doDelete()    {
  router.delete(route('admin.coupons.destroy', deleteTarget.value.id), {
    onFinish: () => { deleteTarget.value = null; }
  });
}
</script>
