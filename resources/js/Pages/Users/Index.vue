<template>
  <AdminLayout title="المستخدمون">
    <div class="flex items-center justify-between mb-6">
      <input v-model="search" type="text" placeholder="اسم أو رقم هاتف..."
        class="form-input w-64" @input="doSearch" />
      <span class="text-slate-500 text-sm">{{ users.total }} مستخدم</span>
    </div>

    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>الاسم</th>
              <th>الهاتف</th>
              <th>الجنس</th>
              <th>العنوان</th>
              <th>الطلبات</th>
              <th>الحالة</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(u, index) in users.data" :key="u.id" :class="!u.is_active ? 'opacity-50' : ''">
              <td class="text-slate-400 text-xs font-mono text-center">
                {{ (users.current_page - 1) * users.per_page + index + 1 }}
              </td>
              <td class="font-medium text-slate-800">{{ u.first_name }} {{ u.last_name }}</td>
              <td dir="ltr" class="font-mono text-slate-600">{{ u.phone }}</td>
              <td>{{ u.gender === 'male' ? '👨 ذكر' : '👩 أنثى' }}</td>
              <td class="text-slate-500 max-w-xs truncate">{{ u.address }}</td>
              <td class="text-center">
                <Link :href="route('admin.orders.index', { search: u.phone })" class="text-primary-600 font-semibold hover:underline">
                  {{ u.orders_count }}
                </Link>
              </td>
              <td>
                <span class="badge" :class="u.is_active ? 'badge-delivered' : 'badge-pending'">
                  {{ u.is_active ? 'نشط' : 'موقوف' }}
                </span>
              </td>
              <td>
                <div class="flex items-center gap-2">
                  <button @click="openPassword(u)" class="btn-secondary py-1 px-2 text-xs">كلمة المرور</button>
                  <button @click="toggleActive(u)" class="btn-secondary py-1 px-2 text-xs"
                    :class="u.is_active ? 'text-amber-600' : 'text-emerald-600'">
                    {{ u.is_active ? 'إيقاف' : 'تفعيل' }}
                  </button>
                  <button @click="deleteUser(u)" class="btn-danger py-1 px-2 text-xs">حذف</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Change Password Modal -->
    <Teleport to="body">
      <div v-if="pwdModal" class="modal-backdrop" @click.self="pwdModal = false">
        <div class="modal-box max-w-md">
          <div class="flex items-center justify-between p-5 border-b">
            <h3 class="font-bold">تغيير كلمة المرور</h3>
            <button @click="pwdModal = false" class="text-xl text-slate-400">&times;</button>
          </div>
          <form @submit.prevent="submitPassword" class="p-5 space-y-4">
            <div class="form-group">
              <label class="form-label">كلمة المرور الجديدة</label>
              <input v-model="pwdForm.password" type="password" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">تأكيد كلمة المرور</label>
              <input v-model="pwdForm.password_confirmation" type="password" class="form-input" required />
            </div>
            <button type="submit" :disabled="pwdForm.processing" class="btn-primary w-full">حفظ</button>
          </form>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ users: Object, filters: Object });
const search = ref(props.filters?.search ?? '');
const pwdModal = ref(false);
const selectedUser = ref(null);
const pwdForm = useForm({ password: '', password_confirmation: '' });

let timer;
function doSearch() {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(route('admin.users.index'), { search: search.value }, { preserveState: true });
  }, 400);
}
function openPassword(u) { selectedUser.value = u; pwdModal.value = true; }
function submitPassword() {
  pwdForm.patch(route('admin.users.password', selectedUser.value.id), {
    onSuccess: () => { pwdModal.value = false; pwdForm.reset(); }
  });
}
function toggleActive(u) {
  router.patch(route('admin.users.toggle-active', u.id), {}, { preserveScroll: true });
}
function deleteUser(u) {
  if (confirm('هل تريد حذف هذا الحساب نهائياً؟')) {
    router.delete(route('admin.users.destroy', u.id));
  }
}
</script>
