<template>
  <AdminLayout title="المنتجات">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3 flex-wrap">
        <input v-model="search" type="text" placeholder="بحث بالاسم أو الكود..."
          class="form-input w-56" @input="doSearch" />
        <select v-model="cat" class="form-select w-44" @change="onCatChange">
          <option value="">كل الأقسام</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <select v-model="subCat" class="form-select w-44" @change="doSearch" :disabled="!cat">
          <option value="">كل الأصناف</option>
          <option v-for="s in availableSubcategories" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
        <!-- Availability Filter -->
        <select v-model="availability" class="form-select w-40" @change="doSearch">
          <option value="">كل التوفر</option>
          <option value="in">متوفر ✅</option>
          <option value="out">نفذ ⚠️</option>
        </select>
        <!-- Out of Stock Badge -->
        <span v-if="outOfStockCount > 0" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
          <i class="fas fa-exclamation-triangle text-amber-500"></i>
          {{ outOfStockCount }} منتج نفذ
        </span>
      </div>
      <Link :href="route('admin.products.create')" class="btn-primary shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-plus ml-2"></i> إضافة منتج
      </Link>
    </div>

    <!-- Table -->
    <div class="card p-0 mb-6">
      <div class="overflow-x-auto min-h-[400px]">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>المنتج</th>
              <th>القسم / الصنف</th>
              <th>السعر</th>
              <th class="text-center">التوفر والمخزون</th>
              <th class="text-center">الظهور</th>
              <th class="text-center">الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, index) in products.data" :key="p.id">
              <!-- Row number -->
              <td class="text-slate-400 text-xs font-mono text-center">
                {{ (products.current_page - 1) * products.per_page + index + 1 }}
              </td>
              
              <!-- Product Info -->
              <td>
                <div class="flex items-center gap-3 w-max">
                  <div class="w-12 h-12 rounded-xl border border-slate-100 overflow-hidden bg-slate-50 shadow-sm shrink-0 flex items-center justify-center">
                    <img v-if="p.first_image && p.first_image.url" :src="p.first_image.url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                    <i v-else class="fas fa-box text-slate-300 text-lg"></i>
                  </div>
                  <div class="flex flex-col">
                    <span class="font-bold text-slate-800 text-[13px]">{{ p.name }}</span>
                    <span v-if="p.sku" class="text-[10px] text-slate-400 font-mono mt-0.5">رمز: {{ p.sku }}</span>
                    <span v-if="p.size" class="text-[11px] text-slate-500 mt-0.5">{{ p.size }}</span>
                  </div>
                </div>
              </td>
              
              <!-- Category & Subcategory -->
              <td>
                <div class="flex flex-col gap-1 w-max">
                  <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-slate-700">
                    <i class="fas fa-folder text-slate-400 text-[10px]"></i>
                    {{ p.category?.name || 'غير محدد' }}
                  </span>
                  <span v-if="p.subcategory" class="inline-flex items-center gap-1 text-[10px] text-primary-600 font-bold bg-primary-50 px-2 py-0.5 rounded-md border border-primary-100/50 w-fit">
                    <i class="fas fa-level-down-alt fa-flip-horizontal"></i>
                    {{ p.subcategory.name }}
                  </span>
                </div>
              </td>
              
              <!-- Price -->
              <td>
                <div v-if="p.is_on_sale && p.sale_price" class="flex flex-col w-max">
                  <span class="text-red-500 font-bold text-[13px]">{{ fmt(p.sale_price) }}</span>
                  <span class="line-through text-slate-400 text-[10px] mt-0.5">{{ fmt(p.price) }}</span>
                </div>
                <div v-else class="w-max">
                  <span class="font-bold text-slate-700 text-[13px]">{{ fmt(p.price) }}</span>
                </div>
              </td>
              
              <!-- Availability -->
              <td class="text-center">
                <button @click="toggleAvailability(p)" class="px-3 py-1 text-[11px] font-bold rounded-full transition-all inline-flex items-center gap-1.5" :class="p.is_available ? 'text-emerald-700 bg-emerald-100/50 hover:bg-emerald-100' : 'text-amber-700 bg-amber-100/50 hover:bg-amber-100'" :title="p.is_available ? 'إيقاف التوفر' : 'تفعيل التوفر'">
                  <span class="w-1.5 h-1.5 rounded-full" :class="p.is_available ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                  {{ p.is_available ? 'متوفر' : 'نفذ' }}
                </button>
              </td>
              
              <!-- Active/Hidden -->
              <td class="text-center">
                <button @click="toggleActive(p)" class="px-3 py-1 text-[11px] font-bold rounded-full transition-all inline-flex items-center gap-1.5" :class="p.is_active ? 'text-blue-700 bg-blue-100/50 hover:bg-blue-100' : 'text-slate-600 bg-slate-100 hover:bg-slate-200'" title="تغيير حالة الظهور">
                  <span class="w-1.5 h-1.5 rounded-full" :class="p.is_active ? 'bg-blue-500' : 'bg-slate-400'"></span>
                  {{ p.is_active ? 'نشط' : 'مخفي' }}
                </button>
              </td>
              
              <!-- Actions -->
              <td>
                <div class="flex items-center justify-center gap-1.5 opacity-100 lg:opacity-30 lg:group-hover:opacity-100 transition-opacity">
                  <Link :href="route('admin.products.edit', p.id)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                    <i class="fas fa-pen text-[11px]"></i>
                    <span class="text-[12px] font-bold">تعديل</span>
                  </Link>
                  <button @click="askDelete(p)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                    <i class="fas fa-trash text-[11px]"></i>
                    <span class="text-[12px] font-bold">حذف</span>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!products.data.length">
              <td colspan="7" class="px-4 py-12 text-center text-slate-500 bg-slate-50/50">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-300">
                  <i class="fas fa-box-open text-2xl"></i>
                </div>
                <p class="font-medium">لا توجد منتجات مضافة حتى الآن أو غير مطابقة لبحثك.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="products.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-slate-100 gap-4 mt-auto">
        <p class="text-slate-500 text-[12px] font-medium">
          عرض <span class="font-bold text-slate-800">{{ (products.current_page-1)*products.per_page+1 }}</span> 
          إلى <span class="font-bold text-slate-800">{{ Math.min(products.current_page*products.per_page, products.total) }}</span>
          من أصل <span class="font-bold text-slate-800">{{ products.total }}</span> منتج
        </p>
        <div class="flex gap-1.5">
          <Link v-for="page in products.links" :key="page.label" :href="page.url ?? '#'"
            class="px-3 py-1.5 rounded-lg text-[12px] font-bold transition-all"
            :class="[
              page.active ? 'bg-primary border-transparent text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-slate-300',
              !page.url ? 'opacity-40 cursor-not-allowed hidden md:inline-block border-transparent' : ''
            ]"
            :disabled="!page.url">
            <span v-html="page.label" />
          </Link>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="!!deleteTarget"
      :title="`حذف المنتج`"
      :message="`هل تريد حذف &quot;${deleteTarget?.name ?? ''}&quot;؟ لا يمكن التراجع عن هذا الإجراء.`"
      confirm-label="حذف"
      @confirm="doDelete"
      @cancel="deleteTarget = null"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout    from '@/Layouts/AdminLayout.vue';
import ConfirmModal   from '@/Components/ConfirmModal.vue';

const props = defineProps({ products: Object, categories: Array, filters: Object, outOfStockCount: { type: Number, default: 0 } });

const search       = ref(props.filters?.search ?? '');
const cat          = ref(props.filters?.category_id ?? '');
const subCat       = ref(props.filters?.subcategory_id ?? '');
const availability = ref(props.filters?.availability ?? '');
const deleteTarget = ref(null);

const availableSubcategories = computed(() => {
  if (!cat.value) return [];
  const category = props.categories.find(c => c.id === cat.value);
  return category?.subcategories || [];
});

let timer;
function doSearch() {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(route('admin.products.index'), {
      search: search.value,
      category_id: cat.value,
      subcategory_id: subCat.value,
      availability: availability.value,
    }, { preserveState: true });
  }, 400);
}

function onCatChange() {
  subCat.value = ''; // Reset subcategory when category changes
  doSearch();
}

function fmt(v) { return Number(v).toLocaleString('ar-IQ') + ' د.ع'; }

function toggleAvailability(p) {
  router.patch(route('admin.products.toggle-availability', p.id), {}, { preserveScroll: true });
}
function toggleActive(p) {
  router.patch(route('admin.products.toggle-active', p.id), {}, { preserveScroll: true });
}
function askDelete(p)  { deleteTarget.value = p; }
function doDelete()    {
  router.delete(route('admin.products.destroy', deleteTarget.value.id), {
    preserveScroll: true,
    onFinish: () => { deleteTarget.value = null; },
  });
}
</script>
