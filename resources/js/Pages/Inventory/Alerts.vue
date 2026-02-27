<template>
  <AdminLayout title="تنبيهات المخزون">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">تنبيهات المخزون</h1>
        <p class="text-slate-500 mt-1">عرض جميع المنتجات التي وصل مخزونها إلى حد التنبيه ({{ lowStockThreshold }}) أو أقل.</p>
      </div>

      <div class="flex items-center gap-3">
        <div class="relative max-w-xs">
          <input 
            type="text" 
            v-model="search" 
            @input="doSearch"
            placeholder="بحث عن منتج..."
            class="form-input pr-10 bg-white border-slate-200 focus:ring-primary-500 focus:border-primary-500"
          >
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          </div>
        </div>
        <Link :href="route('admin.settings.index')" class="btn-secondary text-sm">تعديل حد التنبيه</Link>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th class="w-16">صورة</th>
              <th>المنتج</th>
              <th>التصنيف</th>
              <th class="text-center">المخزون الحالي</th>
              <th class="text-center w-32">الحالة</th>
              <th class="text-left w-24">إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="v in alerts.data" :key="v.id" class="group hover:bg-slate-50 transition-colors">
              <td>
                <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden">
                  <img v-if="v.image" :src="v.image" class="w-full h-full object-cover">
                  <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  </div>
                </div>
              </td>
              <td>
                <div>
                  <Link :href="route('admin.products.edit', v.product_id)" class="font-bold text-slate-800 hover:text-primary-600 transition truncate block max-w-md">
                    {{ v.product_name }}
                  </Link>
                  <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                    <span v-if="v.color" class="bg-slate-100 px-1.5 py-0.5 rounded">لون: {{ v.color }}</span>
                    <span v-if="v.size" class="bg-slate-100 px-1.5 py-0.5 rounded">حجم: {{ v.size }}</span>
                  </p>
                </div>
              </td>
              <td class="text-sm text-slate-600 font-medium">{{ v.category_name }}</td>
              <td class="text-center">
                <div class="inline-flex flex-col items-center">
                  <span class="text-lg font-black" :class="v.stock === 0 ? 'text-red-600' : 'text-orange-500'">{{ v.stock }}</span>
                  <span class="text-[10px] text-slate-400 uppercase tracking-tighter font-bold">قطعة</span>
                </div>
              </td>
              <td class="text-center">
                <span v-if="v.stock === 0" class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-[10px] font-bold border border-red-200 flex items-center justify-center gap-1 mx-auto w-fit">
                   <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> نفذت الكمية
                </span>
                <span v-else class="px-2.5 py-1 rounded-full bg-orange-100 text-orange-700 text-[10px] font-bold border border-orange-200 flex items-center justify-center gap-1 mx-auto w-fit">
                   <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> مخزون منخفض
                </span>
              </td>
              <td class="text-left">
                <Link :href="route('admin.products.edit', v.product_id)" class="btn-primary text-[11px] px-3 py-1.5 shadow-sm">
                  تعديل الكمية
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!alerts.data.length" class="text-center py-20 bg-slate-50/50">
        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </div>
        <h3 class="text-lg font-bold text-slate-800">لا توجد تنبيهات حالياً</h3>
        <p class="text-slate-500 mt-1 max-w-sm mx-auto">كل المنتجات في المتجر تمتلك رصيداً كافياً أعلى من حد التنبيه ({{ lowStockThreshold }}).</p>
      </div>

      <!-- Pagination -->
      <div v-if="alerts.links.length > 3" class="px-6 py-4 border-t border-slate-100 bg-slate-50/30 flex items-center justify-center">
         <div class="flex items-center gap-1">
            <Link 
              v-for="(link, i) in alerts.links" :key="i"
              :href="link.url ?? '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-md text-sm transition-all"
              :class="[
                link.active ? 'bg-primary-600 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-white hover:shadow-sm',
                !link.url ? 'opacity-30 pointer-events-none' : ''
              ]"
            />
         </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  alerts: Object,
  filters: Object,
  lowStockThreshold: Number,
});

const search = ref(props.filters.search ?? '');

const doSearch = debounce(() => {
  router.get(route('inventory.alerts'), { search: search.value }, { preserveState: true, replace: true });
}, 500);
</script>
