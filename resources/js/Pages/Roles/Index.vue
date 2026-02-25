<template>
  <AdminLayout title="إدارة الأدوار والصلاحيات">
    <div class="max-w-5xl space-y-6 pb-12">
      <!-- Header Area -->
      <div class="card bg-slate-900 border-0 text-white shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-transparent pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="bg-white/10 p-3 rounded-2xl flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold font-mono">الأدوار المخصصة (Roles)</h2>
              <p class="text-slate-400 mt-1 text-sm">حدد نوعية الموظفين وما الذي يحق لهم فعله في النظام</p>
            </div>
          </div>
          <button type="button" @click="openCreateModal" class="btn btn-primary shrink-0 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            إضافة دور جديد
          </button>
        </div>
      </div>

      <!-- Roles Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="role in roles" :key="role.id" class="card p-0 overflow-hidden flex flex-col hover:border-indigo-300 transition-colors">
          <div class="p-5 flex-1">
            <div class="flex items-start justify-between mb-4">
              <h3 class="text-lg font-bold text-slate-800">{{ role.name }}</h3>
              <span class="bg-indigo-50 text-indigo-600 text-xs font-bold px-2.5 py-1 rounded-full border border-indigo-100">
                {{ role.admins_count }} موظف
              </span>
            </div>
            
            <div class="text-sm text-slate-500 mb-2 font-semibold">الصلاحيات الممنوحة:</div>
            <div class="flex flex-wrap gap-2">
              <span v-for="perm in (role.permissions || [])" :key="perm" 
                    class="inline-block bg-slate-100 text-slate-600 text-xs px-2 py-1 rounded">
                {{ permissionsList[perm] || perm }}
              </span>
              <span v-if="!role.permissions || role.permissions.length === 0" class="text-slate-400 text-xs italic">
                لا توجد صلاحيات (لا يمكنه فعل شيء)
              </span>
            </div>
          </div>
          
          <div class="border-t border-slate-100 bg-slate-50 p-3 flex justify-end gap-2">
            <button @click="openEditModal(role)" class="btn text-blue-600 bg-blue-50 hover:bg-blue-100 border-none px-3 py-1.5 text-xs">تعديل</button>
            <button v-if="role.admins_count === 0" @click="confirmDelete(role)" class="btn text-red-600 bg-red-50 hover:bg-red-100 border-none px-3 py-1.5 text-xs">حذف</button>
            <button v-else class="btn text-slate-400 bg-slate-50 border-none px-3 py-1.5 text-xs cursor-not-allowed" title="لا يمكن حذفه لوجود موظفين">حذف</button>
          </div>
        </div>

        <div v-if="!roles.length" class="md:col-span-2 lg:col-span-3 card text-center py-12 text-slate-500">
          لا توجد أدوار مخصصة حالياً. يتوفر فقط (المدير العام).
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="isModalOpen" @close="closeModal">
      <div class="p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-6">{{ editingRole ? 'تعديل بيانات الدور' : 'إنشاء دور جديد' }}</h2>
        <form @submit.prevent="submitForm">
          <div class="mb-5">
            <label class="block text-sm font-semibold text-slate-700 mb-2">اسم الدور (مثال: محاسب، كاتب، إلخ)</label>
            <input v-model="form.name" type="text" class="form-input" required :class="{'border-red-500': form.errors.name}">
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div class="mb-5">
            <label class="block text-sm font-semibold text-slate-700 mb-3">تخصيص الصلاحيات:</label>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 grid gap-3">
              <label v-for="(label, key) in permissionsList" :key="key" class="flex items-center gap-3 cursor-pointer p-2 hover:bg-white rounded transition-colors border border-transparent hover:border-slate-200">
                <input type="checkbox" :value="key" v-model="form.permissions" 
                       class="w-5 h-5 text-indigo-600 bg-white border-slate-300 rounded focus:ring-indigo-500">
                <span class="text-slate-700 text-sm font-medium select-none">{{ label }}</span>
              </label>
            </div>
          </div>

          <div class="mt-8 flex justify-end gap-3">
            <button type="button" @click="closeModal" class="btn btn-secondary">إلغاء</button>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              {{ form.processing ? 'جاري الحفظ...' : 'حفظ الدور' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps(['roles', 'permissionsList']);

const isModalOpen = ref(false);
const editingRole = ref(null);

const form = useForm({
  name: '',
  permissions: [],
});

function openCreateModal() {
  editingRole.value = null;
  form.reset();
  form.clearErrors();
  isModalOpen.value = true;
}

function openEditModal(role) {
  editingRole.value = role;
  form.name = role.name;
  form.permissions = role.permissions || [];
  form.clearErrors();
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  setTimeout(() => {
    form.reset();
    editingRole.value = null;
  }, 200);
}

function submitForm() {
  if (editingRole.value) {
    form.put(route('admin.roles.update', editingRole.value.id), {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('admin.roles.store'), {
      onSuccess: () => closeModal(),
    });
  }
}

function confirmDelete(role) {
  if (confirm(`هل أنت متأكد من حذف الدور (${role.name})؟`)) {
    router.delete(route('admin.roles.destroy', role.id), { preserveScroll: true });
  }
}
</script>
