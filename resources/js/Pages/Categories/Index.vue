<template>
  <AdminLayout title="الأقسام والتصنيفات">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
      <div class="flex items-center gap-3 w-full sm:w-auto">
        <div class="relative w-full sm:w-64">
          <input v-model="search" type="text" placeholder="ابحث عن قسم..." class="form-input pl-10 w-full" @input="doSearch" />
          <i class="fas fa-search absolute left-3 top-3.5 text-slate-400"></i>
        </div>
        <span class="text-slate-500 font-medium text-sm whitespace-nowrap bg-slate-100 px-3 py-1.5 rounded-lg">{{ categories.total }} قسم رئيسي</span>
      </div>
      <button @click="openAddCategory" class="btn-primary w-full sm:w-auto shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-plus ml-2"></i> إضافة قسم جديد
      </button>
    </div>

    <!-- MAIN CATEGORIES LIST -->
    <div class="space-y-3">
      <div v-for="(cat, i) in categories.data" :key="cat.id" class="bg-white border border-slate-200/80 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300 relative group">
        <!-- Category Row -->
        <div class="p-3 sm:px-4 sm:py-3 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 cursor-pointer select-none" @click="toggleExpand(cat.id)">
          
          <!-- Category Info -->
          <div class="flex items-center gap-3 flex-1 w-full">
            <div class="w-12 h-12 rounded-lg border border-slate-200 overflow-hidden bg-slate-50 shadow-sm shrink-0 flex items-center justify-center">
              <img v-if="cat.image" :src="'/storage/' + cat.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
              <i v-else class="fas fa-box text-slate-300 text-lg"></i>
            </div>
            
            <div class="flex flex-row items-center gap-3 flex-1">
              <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                {{ cat.name }}
                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold" :class="cat.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                  {{ cat.is_active ? 'نشط' : 'مخفي' }}
                </span>
              </h3>
              <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500 font-medium border-r pr-3 border-slate-200">
                <span class="flex items-center gap-1.5 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100">
                  <i class="fas fa-box-open text-primary-500"></i>
                  {{ cat.products_count }} منتج رئيسي
                </span>
                <span class="flex items-center gap-1.5 bg-blue-50/50 text-blue-600 px-1.5 py-0.5 rounded border border-blue-100/50">
                  <i class="fas fa-layer-group"></i>
                  {{ cat.subcategories.length }} أصناف فرعية
                </span>
              </div>
            </div>
          </div>

          <!-- Category Actions -->
          <div class="flex items-center gap-1.5 lg:ml-3 w-full lg:w-auto justify-end border-t lg:border-t-0 border-slate-50 pt-2 lg:pt-0" @click.stop>
            <button @click="toggleActiveCategory(cat)" class="flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded transition-colors" :class="cat.is_active ? 'text-amber-600 bg-amber-50 hover:bg-amber-100' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100'" :title="cat.is_active ? 'إخفاء القسم' : 'تفعيل القسم'">
              <i class="fas text-[10px]" :class="cat.is_active ? 'fa-eye-slash' : 'fa-eye'"></i>
              <span class="hidden sm:inline">{{ cat.is_active ? 'إخفاء' : 'إظهار' }}</span>
            </button>
            <button @click="openEditCategory(cat)" class="flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">
              <i class="fas fa-pen text-[10px]"></i>
              <span class="hidden sm:inline">تعديل</span>
            </button>
            <button @click="askDeleteCategory(cat)" class="flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded text-red-600 bg-red-50 hover:bg-red-100 transition-colors">
              <i class="fas fa-trash text-[10px]"></i>
              <span class="hidden sm:inline">حذف</span>
            </button>
            
            <!-- Expand Indicator -->
            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors mr-1">
              <i class="fas text-[11px]" :class="isExpanded(cat.id) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
          </div>
        </div>

        <!-- ACCORDION CONTENT (SUBCATEGORIES) -->
        <div v-show="isExpanded(cat.id)" class="border-t border-slate-100 bg-slate-50/50 p-3 sm:p-4 shadow-inner" @click.stop>
          <div class="flex items-center justify-between mb-3 border-b border-slate-200/50 pb-2">
            <h4 class="font-bold text-slate-600 text-[13px] flex items-center gap-1.5">
              <i class="fas fa-level-down-alt fa-flip-horizontal text-primary-400"></i>
              قائمة الأصناف الفرعية لـ {{ cat.name }}
            </h4>
            <button @click="openAddSubcategory(cat)" class="px-2.5 py-1 text-[11px] font-bold text-primary-700 bg-white hover:bg-primary-50 border border-primary-200 rounded shadow-sm transition-colors">
              <i class="fas fa-plus mr-0.5"></i> صنف جديد
            </button>
          </div>

          <!-- Empty Subcategories State -->
          <div v-if="!cat.subcategories.length" class="text-center py-4 bg-white rounded-lg border border-slate-200 border-dashed">
            <div class="w-8 h-8 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-1.5"><i class="fas fa-folder-open text-xs"></i></div>
            <p class="text-slate-500 text-xs font-medium">هذا القسم خالي من الأصناف الفرعية.</p>
          </div>

          <!-- Subcategories Grid -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5">
            <div v-for="sub in cat.subcategories" :key="sub.id" class="bg-white border border-slate-200 rounded-md p-2 flex flex-col gap-2 hover:border-primary-300 transition-colors group/sub shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
              
              <!-- Sub Info Row -->
              <div class="flex items-center gap-2">
                <!-- Image -->
                <div class="w-10 h-10 rounded border border-slate-100 overflow-hidden shrink-0 flex items-center justify-center bg-slate-50">
                  <img v-if="sub.image_url || sub.image" :src="sub.image_url || ('/storage/' + sub.image)" class="w-full h-full object-cover">
                  <i v-else class="fas fa-image text-slate-300 text-xs"></i>
                </div>
                
                <!-- Name and details -->
                <div class="flex-1 min-w-0 flex items-start flex-col gap-1 pr-1">
                  <h5 class="font-bold text-slate-700 text-[12px] truncate w-full">{{ sub.name }}</h5>
                  
                  <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-[9px] px-1.5 py-0.5 rounded font-bold" :class="sub.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                      {{ sub.is_active ? 'نشط' : 'مخفي' }}
                    </span>
                    <p class="text-[10px] text-slate-400 font-medium tracking-tighter border-r pr-2 border-slate-200">
                      {{ sub.products_count || 0 }} منتج مربوط
                    </p>
                  </div>
                </div>
              </div>

              <!-- Sub Actions Row -->
              <div class="flex flex-wrap items-center justify-start gap-1.5 opacity-100 lg:opacity-0 lg:group-hover/sub:opacity-100 transition-opacity pt-2 border-t border-slate-100">
                <button @click="toggleActiveSubcategory(cat, sub)" class="flex items-center gap-1 px-2 py-1 text-[10px] font-bold rounded transition-colors" :class="sub.is_active ? 'text-amber-600 bg-amber-50 hover:bg-amber-100' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100'">
                  <span class="font-bold whitespace-nowrap">{{ sub.is_active ? 'إخفاء' : 'إظهار' }}</span>
                </button>
                <button @click="openEditSubcategory(cat, sub)" class="flex items-center gap-1 px-2 py-1 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="تعديل">
                  <span class="text-[10px] font-bold whitespace-nowrap">تعديل</span>
                </button>
                <button @click="askDeleteSubcategory(cat, sub)" class="flex items-center gap-1 px-2 py-1 rounded bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="حذف">
                  <span class="text-[10px] font-bold whitespace-nowrap">حذف</span>
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Categories Empty State -->
      <div v-if="!categories.data.length" class="card py-16 text-center shadow-sm">
        <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100"><i class="fas fa-box-open text-3xl"></i></div>
        <h3 class="text-xl font-bold text-slate-700 mb-2">لا يوجد أقسام مبنية</h3>
        <p class="text-slate-500 mb-6">ابدأ بناء متجرك الآن عبر إدراج الأقسام والعلامات التجارية.</p>
        <button @click="openAddCategory" class="btn-primary inline-flex"><i class="fas fa-plus ml-2"></i> بناء قسم جديد</button>
      </div>

      <!-- Pagination -->
      <div v-if="categories.links && categories.links.length > 3" class="mt-6 flex justify-center pb-8">
        <div class="inline-flex items-center gap-1 bg-white p-1 rounded-xl shadow-sm border border-slate-200">
          <template v-for="(link, i) in categories.links" :key="i">
            <button 
              @click="link.url ? router.get(link.url) : null"
              v-html="link.label"
              class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
              :class="[
                link.active ? 'bg-primary-50 text-primary-700' : 'text-slate-600 hover:bg-slate-50',
                !link.url ? 'opacity-40 cursor-not-allowed hidden md:inline-block' : ''
              ]"
              :disabled="!link.url"
            ></button>
          </template>
        </div>
      </div>
    </div>

    <!-- MAIN MODALS -->
    <Teleport to="body">
      
      <!-- Category Modal -->
      <div v-if="showCatModal" class="modal-backdrop z-[60]" @click.self="closeCatModal">
        <div class="modal-box max-w-md shadow-2xl scale-100 animate-in fade-in zoom-in-95 duration-200">
          <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50/50 rounded-t-2xl">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
              <i class="fas" :class="editCatItem ? 'fa-edit text-blue-500' : 'fa-plus text-primary-500'"></i> 
              {{ editCatItem ? 'تعديل بيانات القسم' : 'إضافة قسم جديد' }}
            </h3>
            <button @click="closeCatModal" class="text-slate-400 hover:text-red-500 text-xl w-8 h-8 rounded-full flex items-center justify-center bg-white border border-slate-200 shadow-sm transition-colors">&times;</button>
          </div>
          <form @submit.prevent="submitCatForm" class="p-6 space-y-5">
            <div class="form-group">
              <label class="form-label font-semibold text-slate-700">الاسم *</label>
              <input v-model="catForm.name" class="form-input" required placeholder="مثال: قسم العصائر أو أجهزة منزلية" />
              <p v-if="catForm.errors.name" class="text-red-500 text-xs mt-1">{{ catForm.errors.name }}</p>
            </div>
            
            <div class="form-group">
              <label class="form-label font-semibold text-slate-700">{{ editCatItem ? 'تغيير الصورة (اختياري)' : 'الصورة الافتراضية *' }}</label>
              <div class="image-drop-zone border-2 border-dashed border-slate-300 hover:border-primary-400 bg-slate-50 transition-colors rounded-xl cursor-pointer" @click="$refs.catImgInput.click()">
                <img v-if="catPreview" :src="catPreview" class="w-full h-32 object-contain rounded-lg mx-auto" />
                <div v-else class="py-6 flex flex-col items-center text-slate-400">
                  <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-3">
                    <i class="fas fa-cloud-upload-alt text-2xl text-primary-400"></i>
                  </div>
                  <span class="text-sm font-medium text-slate-600">انقر هنا لرفع أو تغيير الصورة</span>
                  <span class="text-xs mt-1">PNG, JPG, WEBP (حجم أقصى 2MB)</span>
                </div>
                <input ref="catImgInput" type="file" accept="image/*" class="hidden" @change="onCatImg" />
              </div>
              <p v-if="catForm.errors.image" class="text-red-500 text-xs mt-1">{{ catForm.errors.image }}</p>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-slate-100">
              <button type="submit" :disabled="catForm.processing" class="btn-primary flex-1 py-2.5">
                <i class="fas fa-spinner fa-spin mr-2" v-if="catForm.processing"></i>
                {{ editCatItem ? 'حفظ التعديلات' : 'اعتماد وإنشاء القسم' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Subcategory Modal -->
      <div v-if="showSubModal" class="modal-backdrop z-[60]" @click.self="closeSubModal">
        <div class="modal-box max-w-md shadow-2xl scale-100 animate-in fade-in zoom-in-95 duration-200">
          <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50/50 rounded-t-2xl">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
              <i class="fas" :class="editSubItem ? 'fa-pen text-blue-500' : 'fa-plus text-primary-500'"></i> 
              {{ editSubItem ? 'تعديل صنف فرعي' : 'إضافة صنف جديد لمنطقة: ' + currentCategoryForSub?.name }}
            </h3>
            <button @click="closeSubModal" class="text-slate-400 hover:text-red-500 text-xl w-8 h-8 rounded-full flex items-center justify-center bg-white border border-slate-200 shadow-sm">&times;</button>
          </div>
          <form @submit.prevent="submitSubForm" class="p-6 space-y-5">
            <div class="form-group">
              <label class="form-label font-semibold text-slate-700">اسم الصنف الفرعي *</label>
              <input v-model="subForm.name" class="form-input" required placeholder="مثال: مياه غازية، فواكه، إلخ" />
              <p v-if="subForm.errors.name" class="text-red-500 text-xs mt-1">{{ subForm.errors.name }}</p>
            </div>
            
            <div class="form-group">
              <label class="form-label font-semibold text-slate-700">{{ editSubItem ? 'صورة جديدة (اختياري)' : 'الصورة المطلوبة للصنف *' }}</label>
              <div class="flex items-center gap-4">
                <div class="w-24 h-24 rounded-2xl border-2 border-slate-200 overflow-hidden bg-slate-50 flex items-center justify-center shrink-0 shadow-inner group relative cursor-pointer" @click="$refs.subImgInput.click()">
                  <img v-if="subPreview" :src="subPreview" class="w-full h-full object-cover group-hover:opacity-75 transition-opacity">
                  <i v-else class="fas fa-image text-slate-300 text-3xl"></i>
                  <div class="absolute inset-0 bg-black/40 text-white flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <i class="fas fa-camera mb-1"></i><span class="text-[10px] font-bold">تغيير</span>
                  </div>
                </div>
                <div class="flex-1">
                  <input ref="subImgInput" type="file" @change="onSubImg" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition-colors file:cursor-pointer">
                  <p class="text-xs text-slate-400 mt-2 leading-relaxed">اسمح للزبائن بالتعرف على الصنف أسرع بصورة واضحة. يفضل مقاس مربع 1:1.</p>
                </div>
              </div>
              <p v-if="subForm.errors.image" class="text-red-500 text-xs mt-1">{{ subForm.errors.image }}</p>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-slate-100">
              <button type="submit" :disabled="subForm.processing" class="btn-primary flex-1 py-2.5">
                <i class="fas fa-spinner fa-spin mr-2" v-if="subForm.processing"></i>
                {{ editSubItem ? 'تحديث الصنف التابع' : 'إضافة في الصنف' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Confirm Modals -->
    <ConfirmModal
      :show="!!catDeleteTarget"
      title="حذف القسم بالكامل"
      :message="`هل أنت متأكد من حذف قسم &quot;${catDeleteTarget?.name}&quot; بجميع بياناته والصور التابعة له بشكل نهائي؟`"
      confirm-label="نعم، حذف النهائي"
      @confirm="doDeleteCategory"
      @cancel="catDeleteTarget = null"
    />

    <ConfirmModal
      :show="!!subDeleteTarget"
      title="حذف الصنف الفرعي"
      :message="`احذر: هل تريد حقاً إزالة الصنف &quot;${subDeleteTarget?.sub?.name}&quot; المندرج تحت قسم (${subDeleteTarget?.cat?.name}) بشكل نهائي؟`"
      confirm-label="نعم، إزالة الصنف"
      @confirm="doDeleteSubcategory"
      @cancel="subDeleteTarget = null"
    />

  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout   from '@/Layouts/AdminLayout.vue';
import ConfirmModal  from '@/Components/ConfirmModal.vue';
import StatusBadge   from '@/Components/StatusBadge.vue';

const props = defineProps({
  categories: Object,
  filters: Object
});

// ── Search & UI State ──
const search   = ref(props.filters?.search ?? '');
const expanded = ref([]);
let searchTimer;

function doSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    router.get(route('admin.categories.index'), { search: search.value }, { preserveState: true });
  }, 400);
}

function toggleExpand(id) {
  const i = expanded.value.indexOf(id);
  if (i > -1) {
    expanded.value.splice(i, 1);
  } else {
    expanded.value.push(id);
  }
}

function isExpanded(id) {
  return expanded.value.includes(id);
}

// ── CATEGORIES LOGIC ──
const showCatModal    = ref(false);
const editCatItem     = ref(null);
const catPreview      = ref(null);
const catDeleteTarget = ref(null);

const catForm = useForm({ name: '', image: null, _method: 'POST' });

function onCatImg(e) {
  if(e.target.files[0]) {
    catForm.image = e.target.files[0];
    catPreview.value = URL.createObjectURL(catForm.image);
  }
}

function openAddCategory() {
  editCatItem.value = null; 
  catForm.reset(); 
  catForm.clearErrors();
  catPreview.value = null; 
  showCatModal.value = true;
}

function openEditCategory(cat) {
  editCatItem.value = cat; 
  catForm.reset();
  catForm.clearErrors();
  catForm.name = cat.name; 
  catForm.image = null;
  catPreview.value = cat.image ? ('/storage/' + cat.image) : null; 
  showCatModal.value = true;
}

function closeCatModal() {
  showCatModal.value = false; editCatItem.value = null; catForm.reset(); catPreview.value = null;
}

function submitCatForm() {
  const url = editCatItem.value
    ? route('admin.categories.update', editCatItem.value.id)
    : route('admin.categories.store');
  catForm._method = editCatItem.value ? 'PUT' : 'POST';
  catForm.post(url, { onSuccess: closeCatModal });
}

function toggleActiveCategory(cat) {
  router.post(route('admin.categories.toggle-active', cat.id), {}, { preserveScroll: true });
}

function askDeleteCategory(cat) { catDeleteTarget.value = cat; }
function doDeleteCategory() {
  router.delete(route('admin.categories.destroy', catDeleteTarget.value.id), {
    onFinish: () => { catDeleteTarget.value = null; },
    preserveScroll: true
  });
}

// ── SUBCATEGORIES LOGIC ──
const showSubModal         = ref(false);
const currentCategoryForSub= ref(null);
const editSubItem          = ref(null);
const subPreview           = ref(null);
const subDeleteTarget      = ref(null);

const subForm = useForm({ id: null, name: '', image: null });

function onSubImg(e) {
  if(e.target.files[0]) {
    subForm.image = e.target.files[0];
    subPreview.value = URL.createObjectURL(subForm.image);
  }
}

function openAddSubcategory(cat) {
  currentCategoryForSub.value = cat;
  editSubItem.value = null;
  subForm.reset();
  subForm.clearErrors();
  subPreview.value = null;
  showSubModal.value = true;
}

function openEditSubcategory(cat, sub) {
  currentCategoryForSub.value = cat;
  editSubItem.value = sub;
  subForm.reset();
  subForm.clearErrors();
  subForm.id = sub.id;
  subForm.name = sub.name;
  subForm.image = null;
  subPreview.value = sub.image_url || ('/storage/' + sub.image);
  showSubModal.value = true;
}

function closeSubModal() {
  showSubModal.value = false;
  editSubItem.value = null;
  currentCategoryForSub.value = null;
  subForm.reset();
  subPreview.value = null;
}

function submitSubForm() {
  if (editSubItem.value) {
    subForm.post(route('admin.categories.subcategories.update', { category: currentCategoryForSub.value.id, subcategory: subForm.id }), {
      forceFormData: true,
      onSuccess: closeSubModal,
      preserveScroll: true,
      headers: { 'X-HTTP-Method-Override': 'PUT' }, // Laravel spoofing for multipart bounds over PUT
    });
  } else {
    subForm.post(route('admin.categories.subcategories.store', currentCategoryForSub.value.id), {
      onSuccess: () => {
        closeSubModal();
        // optionally auto expand if closed
        if(!isExpanded(currentCategoryForSub.value.id)) toggleExpand(currentCategoryForSub.value.id);
      },
      preserveScroll: true
    });
  }
}

function toggleActiveSubcategory(cat, sub) {
  router.post(route('admin.categories.subcategories.toggle-active', { category: cat.id, subcategory: sub.id }), {}, { preserveScroll: true });
}

function askDeleteSubcategory(cat, sub) {
  subDeleteTarget.value = { cat, sub };
}

function doDeleteSubcategory() {
  if (!subDeleteTarget.value) return;
  const { cat, sub } = subDeleteTarget.value;
  router.delete(route('admin.categories.subcategories.destroy', { category: cat.id, subcategory: sub.id }), {
    onSuccess: () => { subDeleteTarget.value = null; },
    preserveScroll: true
  });
}
</script>
