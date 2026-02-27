<template>
  <AdminLayout title="الأقضية تقسيم المحافظات">
    <div class="max-w-3xl">
      <!-- Add form -->
      <div class="card mb-4 min-w-max">
        <form @submit.prevent="addDistrict" class="flex gap-3">
          <select v-model="form.governorate_id" class="form-input flex-1 max-w-xs" required>
            <option value="" disabled>اختر المحافظة...</option>
            <option v-for="g in governorates" :key="g.id" :value="g.id">{{ g.name }}</option>
          </select>
          <input v-model="form.name" type="text" placeholder="اسم القضاء الجديد..." class="form-input flex-1" required />
          <button type="submit" :disabled="form.processing" class="btn-primary">
            + إضافة قضاء
          </button>
        </form>
      </div>

      <!-- Governorates Accordion -->
      <div v-if="governorates.length" class="space-y-3">
        <div v-for="(g, i) in governorates" :key="g.id" class="card p-0 overflow-hidden">
          
          <!-- Governorate Header (Click to expand/collapse) -->
          <div 
            @click="toggleGovernorate(g.id)"
            class="flex items-center justify-between p-4 cursor-pointer hover:bg-slate-50 transition-colors bg-white select-none border-b border-transparent"
            :class="{'border-slate-100 bg-slate-50': isExpanded(g.id)}"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                {{ i + 1 }}
              </div>
              <div>
                <h3 class="font-bold text-slate-800 text-base">{{ g.name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                  <span v-if="g.districts.length === 0">لا توجد أقضية مضافة</span>
                  <span v-else>{{ g.districts.length }} أقضية مضافة</span>
                </p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <span class="badge" :class="g.is_active ? 'badge-delivered' : 'badge-pending'">
                  {{ g.is_active ? 'نشط' : 'موقوف' }}
              </span>
              <svg 
                class="w-5 h-5 text-slate-400 transition-transform duration-300" 
                :class="{'rotate-180': isExpanded(g.id)}"
                fill="none" viewBox="0 0 24 24" stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>

          <!-- Districts List (Collapsible Content) -->
          <div v-show="isExpanded(g.id)" class="bg-slate-50/50 p-4 pt-0 border-t border-slate-100">
            <table v-if="g.districts.length > 0" class="w-full text-right mt-4 rounded-lg overflow-hidden border border-slate-200 bg-white">
              <thead class="bg-slate-100 border-b border-slate-200">
                <tr>
                  <th class="px-4 py-3 text-xs font-semibold tracking-wide text-slate-500 w-12 text-center">#</th>
                  <th class="px-4 py-3 text-xs font-semibold tracking-wide text-slate-500">اسم القضاء</th>
                  <th class="px-4 py-3 text-xs font-semibold tracking-wide text-slate-500">الطلبات</th>
                  <th class="px-4 py-3 text-xs font-semibold tracking-wide text-slate-500 text-center">الحالة</th>
                  <th class="px-4 py-3 text-xs font-semibold tracking-wide text-slate-500 text-center">إجراء</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="(d, j) in g.districts" :key="d.id" class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-4 py-3 text-slate-400 text-xs font-mono text-center">{{ j + 1 }}</td>
                  <td class="px-4 py-3 font-semibold text-slate-700 text-sm">{{ d.name }}</td>
                  <td class="px-4 py-3">
                    <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                      {{ d.orders_count }} طلب
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <button @click="toggleActive(d)"
                      class="px-2 py-1 text-xs font-medium rounded-md border"
                      :class="d.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                      {{ d.is_active ? 'نشط' : 'موقوف' }}
                    </button>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <button @click="askDelete(d)"
                      class="inline-flex items-center justify-center p-1.5 
                             text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <div v-else class="text-center py-6 text-slate-400 text-sm bg-white rounded-lg border border-slate-200 mt-4">
              لا توجد أقضية في هذه المحافظة. 
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="card text-center py-10">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <p class="text-slate-500 font-medium">لا توجد محافظات مضافة</p>
        <p class="text-slate-400 text-sm mt-1">يجب إضافة محافظات أولاً من قائمة المحافظات قبل البدء بخلق أقضية.</p>
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

const props = defineProps({ governorates: Array });

const expandedGovernorates = ref([]);
const deleteTarget = ref(null);
const form = useForm({ name: '', governorate_id: '' });

// Toggle accordion
function toggleGovernorate(id) {
  const index = expandedGovernorates.value.indexOf(id);
  if (index > -1) {
    expandedGovernorates.value.splice(index, 1);
  } else {
    expandedGovernorates.value.push(id);
  }
}

// Check if governorate is expanded
function isExpanded(id) {
  return expandedGovernorates.value.includes(id);
}

// Add new district and ensure its governorate is expanded
function addDistrict() {
  const selectedGovId = form.governorate_id;
  form.post(route('admin.districts.store'), { 
    onSuccess: () => { 
      form.reset('name');
      if (!isExpanded(selectedGovId)) {
        expandedGovernorates.value.push(selectedGovId);
      }
    } 
  });
}

function toggleActive(d) {
  router.patch(route('admin.districts.toggle-active', d.id), {}, { preserveScroll: true });
}

function askDelete(d) { deleteTarget.value = d; }

function doDelete() {
  router.delete(route('admin.districts.destroy', deleteTarget.value.id), {
    onFinish: () => { deleteTarget.value = null; },
    preserveScroll: true
  });
}
</script>
