<template>
  <AdminLayout title="إدارة المشرفين">
    <div class="max-w-5xl space-y-6 pb-12">
      <!-- Header Area -->
      <div class="card bg-slate-900 border-0 text-white shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-transparent pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="bg-white/10 p-3 rounded-2xl flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold font-mono">حسابات الإدارة (Admins)</h2>
              <p class="text-slate-400 mt-1 text-sm">إدارة حسابات الموظفين ومنحهم الأدوار والصلاحيات</p>
            </div>
          </div>
          <button type="button" @click="openCreateModal" class="btn bg-blue-600 hover:bg-blue-500 text-white border-none shrink-0 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            إضافة مشرف جديد
          </button>
        </div>
      </div>

      <!-- Admins List -->
      <div class="card p-0 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-right bg-white">
            <thead class="bg-slate-50 border-b border-slate-100 text-slate-500">
              <tr>
                <th class="p-4 font-bold rounded-tr-xl">اسم الموظف</th>
                <th class="p-4 font-bold">البريد الإلكتروني</th>
                <th class="p-4 font-bold">الدور المخصص</th>
                <th class="p-4 font-bold rounded-tl-xl text-center">إجراءات</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80">
              <tr v-for="admin in admins" :key="admin.id" class="hover:bg-slate-50/50 transition duration-150">
                <td class="p-4 font-bold text-slate-800">{{ admin.name }}</td>
                <td class="p-4 text-slate-500 font-mono" dir="ltr">{{ admin.email }}</td>
                <td class="p-4">
                  <span v-if="admin.role" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                    {{ admin.role.name }}
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    مدير عام (كافة الصلاحيات)
                  </span>
                </td>
                <td class="p-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button @click="openEditModal(admin)" class="btn text-blue-600 bg-blue-50 hover:bg-blue-100 border-none px-3 text-xs py-1.5" title="تعديل">
                      تعديل
                    </button>
                    <button v-if="admin.id !== authAdminId" @click="confirmDelete(admin)" class="btn text-red-600 bg-red-50 hover:bg-red-100 border-none px-3 text-xs py-1.5" title="حذف">
                      حذف
                    </button>
                    <button v-else class="btn text-slate-400 bg-slate-50 border-none px-3 text-xs py-1.5 cursor-not-allowed" title="لا يمكنك حذف نفسك">
                      أنت
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="isModalOpen" @close="closeModal">
      <div class="p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-6">{{ editingAdmin ? 'تعديل بيانات المشرف' : 'تسجيل مشرف جديد' }}</h2>
        <form @submit.prevent="submitForm">
          <div class="mb-4">
            <label class="block text-sm font-semibold text-slate-700 mb-1">الاسم الكامل</label>
            <input v-model="form.name" type="text" class="form-input" required placeholder="مثال: أحمد علي">
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-semibold text-slate-700 mb-1">البريد الإلكتروني (تسجيل الدخول)</label>
            <input v-model="form.email" type="email" class="form-input font-mono text-left" dir="ltr" required placeholder="admin@example.com">
            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-semibold text-slate-700 mb-1">
              كلمة المرور
              <span v-if="editingAdmin" class="text-amber-500 text-xs font-normal"> (اتركه فارغاً إذا لم ترد تغييره)</span>
            </label>
            <input v-model="form.password" type="password" class="form-input font-mono text-left" dir="ltr" :required="!editingAdmin">
            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-semibold text-slate-700 mb-1">الدور والصلاحيات</label>
            <select v-model="form.role_id" class="form-select">
              <option :value="null">مدير عام (دخول كامل بدون قيود)</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">مُحدد: {{ role.name }}</option>
            </select>
            <p v-if="form.errors.role_id" class="text-red-500 text-xs mt-1">{{ form.errors.role_id }}</p>
          </div>

          <div class="mt-8 flex justify-end gap-3">
            <button type="button" @click="closeModal" class="btn btn-secondary">إلغاء</button>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              {{ form.processing ? 'جاري الحفظ...' : 'حفظ البيانات' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps(['admins', 'roles']);

const page = usePage();
const authAdminId = computed(() => page.props.auth?.admin?.id);

const isModalOpen = ref(false);
const editingAdmin = ref(null);

const form = useForm({
  name: '',
  email: '',
  password: '',
  role_id: null,
});

function openCreateModal() {
  editingAdmin.value = null;
  form.reset();
  form.clearErrors();
  isModalOpen.value = true;
}

function openEditModal(admin) {
  editingAdmin.value = admin;
  form.name = admin.name;
  form.email = admin.email;
  form.password = '';
  form.role_id = admin.role_id;
  form.clearErrors();
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  setTimeout(() => {
    form.reset();
    editingAdmin.value = null;
  }, 200);
}

function submitForm() {
  if (editingAdmin.value) {
    form.put(route('admin.admins.update', editingAdmin.value.id), {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('admin.admins.store'), {
      onSuccess: () => closeModal(),
    });
  }
}

function confirmDelete(admin) {
  if (confirm(`هل أنت متأكد من حذف الحساب الإداري (${admin.name}) بالكامل؟ لا يمكن التراجع عن هذا.`)) {
    router.delete(route('admin.admins.destroy', admin.id), { preserveScroll: true });
  }
}
</script>
