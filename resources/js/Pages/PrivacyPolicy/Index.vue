<template>
  <AdminLayout title="سياسة الخصوصية والتطبيق">
    <div class="max-w-4xl mx-auto space-y-6 pb-12">
      
      <!-- Header Area -->
      <div class="card bg-slate-900 border-0 text-white shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-transparent pointer-events-none"></div>
        <div class="relative flex flex-col items-start gap-6">
          <div class="flex items-center gap-4">
            <div class="bg-white/10 p-3 rounded-2xl flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold font-mono">سياسة الخصوصية أو الشروط والأحكام</h2>
              <p class="text-slate-400 mt-1 text-sm">عدّل النص الذي يظهر للزبائن داخل تطبيق الموبايل بخصوص شروط استخدام خدمتكم</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Editor Area -->
      <div class="card p-0 shadow-sm border-0 bg-white overflow-hidden">
        <form @submit.prevent="save">
          
          <!-- Editor Toolbar -->
          <div class="bg-slate-50 border-b border-slate-100 p-3 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
              <button type="button" @click="formatText('bold')" title="نص عريض" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-white rounded-lg border border-transparent hover:border-slate-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path></svg>
              </button>
              <div class="w-px h-6 bg-slate-200 mx-1"></div>
              <button type="button" @click="insertLine('## ')" title="عنوان فرعي" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-white rounded-lg border border-transparent hover:border-slate-200 transition font-bold font-mono text-sm leading-none flex items-center justify-center">
                H2
              </button>
              <button type="button" @click="insertLine('- ')" title="قائمة غير مرتبة" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-white rounded-lg border border-transparent hover:border-slate-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
              </button>
              <button type="button" @click="insertLine('---')" title="خط فاصل" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-white rounded-lg border border-transparent hover:border-slate-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              </button>
            </div>
            
            <div class="text-xs font-semibold text-slate-400 bg-white border border-slate-200 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1.5 cursor-default hover:text-slate-600 transition">
              <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
              مدعوم بنظام Markdown
            </div>
          </div>

          <!-- The Textarea -->
          <div class="relative">
            <textarea
              ref="editorRef"
              v-model="content"
              rows="24"
              class="w-full form-input border-0 font-mono text-sm leading-8 resize-y p-6 text-slate-700 bg-white focus:ring-0 focus:bg-slate-50 transition-colors"
              dir="rtl"
              placeholder="اكتب سياسة الخصوصية والشروط هنا...

## مقدمة
نحن في أمواج ديالى نهتم بخصوصيتك ونحرص على توفير أفضل تجربة لك...

## المعلومات التي نجمعها
- الاسم الكامل وعنوان التوصيل لضمان وصول المياه في الوقت المناسب.
- رقم الهاتف للتواصل معك عند وصول المندوب.

---
يرجى العلم أنك باستخدامك للتطبيق فأنت توافق على كل شروط الاستخدام.">
            </textarea>
            
            <!-- Floating word count -->
            <div class="absolute bottom-4 left-4 pointer-events-none">
              <div class="bg-slate-900/60 backdrop-blur-sm text-white text-[11px] font-mono px-3 py-1.5 rounded-full">
                {{ content.length }} حرف
              </div>
            </div>
          </div>

          <!-- Bottom Actions -->
          <div class="bg-slate-50 p-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <button type="button" @click="showPreview = !showPreview" 
              class="w-full sm:w-auto px-6 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-100 hover:border-slate-300 transition-all flex items-center justify-center gap-2 shadow-sm">
              <svg v-if="!showPreview" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              {{ showPreview ? 'أخفِ المعاينة واستمر بالتعديل' : 'معاينة شكل النص في التطبيق' }}
            </button>
            
            <div class="flex flex-row-reverse sm:flex-row items-center w-full sm:w-auto gap-3">
              <transition name="fade">
                <div v-if="saved" class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                  <span class="text-xs font-bold">تم الحفظ عبر السيرفر</span>
                </div>
              </transition>
              
              <button type="submit" :disabled="form.processing" class="w-full sm:w-auto btn bg-blue-600 hover:bg-blue-500 text-white font-bold px-8 shadow-lg shadow-blue-500/30 flex justify-center items-center gap-2">
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                {{ form.processing ? 'جاري الحفظ...' : 'حفظ ونشر التعديلات' }}
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Markdown Live Preview Panel -->
      <transition 
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-4 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-4 opacity-0"
      >
        <div v-show="showPreview" class="relative group mt-8">
          <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
          <div class="relative bg-white border border-slate-100 rounded-2xl p-6 md:p-10 shadow-xl">
            <!-- Mobile App Device simulation header -->
            <div class="absolute top-0 inset-x-0 h-12 bg-slate-50 border-b border-slate-100 rounded-t-2xl flex items-center justify-center pointer-events-none">
              <div class="w-16 h-1.5 bg-slate-200 rounded-full"></div>
            </div>
            
            <div class="mt-8 prose prose-slate max-w-none text-[15px] leading-8" dir="rtl" v-html="rendered">
            </div>
          </div>
        </div>
      </transition>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ content: String });

const content     = ref(props.content ?? '');
const showPreview = ref(false);
const saved       = ref(false);
const editorRef   = ref(null);

const form = useForm({ content: '' });

// Simple markdown → HTML for preview
const rendered = computed(() => {
  let html = content.value
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/^## (.+)$/gm, '<h2 class="text-xl font-bold text-slate-800 mt-8 mb-4 border-b pb-2 border-slate-100">$1</h2>')
    .replace(/^# (.+)$/gm, '<h1 class="text-2xl font-black text-slate-900 mt-6 mb-4 text-center">$1</h1>')
    .replace(/^- (.+)$/gm, '<li class="text-slate-600 mb-2 mr-4 relative before:content-[\'\'] before:w-1.5 before:h-1.5 before:bg-blue-500 before:rounded-full before:absolute before:-right-4 before:top-2.5">$1</li>')
    .replace(/^---$/gm, '<hr class="my-8 border-slate-100">')
    .replace(/\n/g, '<br>');
  return html;
});

function formatText(type) {
  const el = editorRef.value;
  if (!el) return;
  const start = el.selectionStart;
  const end   = el.selectionEnd;
  const sel   = content.value.substring(start, end);
  if (type === 'bold') {
    content.value = content.value.substring(0, start) + `**${sel}**` + content.value.substring(end);
  }
}

function insertLine(prefix) {
  const el = editorRef.value;
  if (!el) return;
  const pos = el.selectionStart;
  content.value = content.value.substring(0, pos) + '\n' + prefix + content.value.substring(pos);
}

function save() {
  form.content = content.value;
  form.post(route('admin.privacy-policy.update'), {
    onSuccess: () => { saved.value = true; setTimeout(() => (saved.value = false), 3000); },
  });
}
</script>
