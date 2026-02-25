<template>
  <AdminLayout title="المنتجات">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <input v-model="search" type="text" placeholder="بحث بالاسم..."
          class="form-input w-56" @input="doSearch" />
        <select v-model="cat" class="form-select w-44" @change="doSearch">
          <option value="">كل الأقسام</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <Link :href="route('admin.products.create')" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        إضافة منتج
      </Link>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>المنتج</th>
              <th>القسم</th>
              <th>السعر</th>
              <th>التوفر</th>
              <th>الظهور</th>
              <th>الإجراءات</th>
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
                <div class="flex items-center gap-3">
                  <!-- Image or placeholder -->
                  <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0">
                    <img v-if="p.first_image && p.first_image.url"
                      :src="p.first_image.url"
                      class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/>
                      </svg>
                    </div>
                  </div>
                  <div>
                    <p class="font-medium text-slate-800">{{ p.name }}</p>
                    <p v-if="p.size" class="text-xs text-slate-400">{{ p.size }}</p>
                  </div>
                </div>
              </td>
              <td class="text-slate-500">{{ p.category?.name }}</td>
              <!-- Price -->
              <td>
                <div v-if="p.is_on_sale && p.sale_price">
                  <p class="line-through text-slate-400 text-xs">{{ fmt(p.price) }}</p>
                  <p class="text-red-600 font-semibold text-sm">{{ fmt(p.sale_price) }}</p>
                </div>
                <p v-else class="font-medium">{{ fmt(p.price) }}</p>
              </td>
              <!-- Availability Toggle -->
              <td>
                <button @click="toggleAvailability(p)"
                  class="badge cursor-pointer transition-all"
                  :class="p.is_available ? 'badge-delivered' : 'badge-pending'">
                  {{ p.is_available ? '✅ متوفر' : '❌ غير متوفر' }}
                </button>
              </td>
              <!-- Active Toggle -->
              <td>
                <button @click="toggleActive(p)"
                  class="badge cursor-pointer transition-all"
                  :class="p.is_active ? 'badge-received' : 'bg-slate-100 text-slate-500'">
                  {{ p.is_active ? '👁 نشط' : '🙈 مخفي' }}
                </button>
              </td>
              <!-- Actions -->
              <td>
                <div class="flex items-center gap-2">
                  <Link :href="route('admin.products.edit', p.id)" class="btn-secondary py-1.5 px-3 text-xs">
                    ✏️ تعديل
                  </Link>
                  <button @click="askDelete(p)" class="btn-danger py-1.5 px-3 text-xs">
                    🗑 حذف
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="!products.data.length" class="text-slate-400 text-sm text-center py-10">
          لا توجد منتجات
        </p>
      </div>

      <!-- Pagination -->
      <div v-if="products.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-slate-100">
        <p class="text-slate-500 text-sm">
          عرض {{ (products.current_page-1)*products.per_page+1 }}–{{ Math.min(products.current_page*products.per_page, products.total) }}
          من {{ products.total }} منتج
        </p>
        <div class="flex gap-1">
          <Link v-for="page in products.links" :key="page.label" :href="page.url ?? '#'"
            class="px-3 py-1.5 rounded-lg text-sm transition"
            :class="page.active ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
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
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout    from '@/Layouts/AdminLayout.vue';
import ConfirmModal   from '@/Components/ConfirmModal.vue';

const props = defineProps({ products: Object, categories: Array, filters: Object });

const search       = ref(props.filters?.search ?? '');
const cat          = ref(props.filters?.category_id ?? '');
const deleteTarget = ref(null);

let timer;
function doSearch() {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(route('admin.products.index'), { search: search.value, category_id: cat.value }, { preserveState: true });
  }, 400);
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
