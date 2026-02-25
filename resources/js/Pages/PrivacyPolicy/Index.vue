<template>
  <AdminLayout title="سياسة الخصوصية">
    <div class="max-w-3xl">
      <div class="card">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h2 class="font-bold text-slate-800 text-lg">سياسة الخصوصية</h2>
            <p class="text-slate-400 text-sm mt-0.5">تُعرض هذه السياسة للزبائن داخل التطبيق</p>
          </div>
          <!-- Preview badge -->
          <span class="text-xs bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1.5 rounded-lg font-medium">
            📱 تظهر في التطبيق
          </span>
        </div>

        <form @submit.prevent="save">
          <!-- Toolbar -->
          <div class="flex flex-wrap gap-1.5 mb-2 pb-2 border-b border-slate-100">
            <button type="button" @click="formatText('bold')"
              class="px-2.5 py-1 text-xs font-bold border border-slate-200 rounded-md hover:bg-slate-50 transition">
              B
            </button>
            <button type="button" @click="insertLine('## ')"
              class="px-2.5 py-1 text-xs border border-slate-200 rounded-md hover:bg-slate-50 transition font-semibold">
              عنوان
            </button>
            <button type="button" @click="insertLine('- ')"
              class="px-2.5 py-1 text-xs border border-slate-200 rounded-md hover:bg-slate-50 transition">
              • قائمة
            </button>
            <button type="button" @click="insertLine('---')"
              class="px-2.5 py-1 text-xs border border-slate-200 rounded-md hover:bg-slate-50 transition">
              سطر فاصل
            </button>
          </div>

          <!-- Text Area -->
          <textarea
            ref="editorRef"
            v-model="content"
            rows="22"
            class="form-input font-mono text-sm leading-7 w-full resize-y"
            dir="rtl"
            placeholder="اكتب سياسة الخصوصية هنا...

## مقدمة
نحن في أمواج ديالى نهتم بخصوصيتك...

## المعلومات التي نجمعها
- الاسم الكامل
- رقم الهاتف
...">
          </textarea>

          <!-- Preview toggle -->
          <div class="flex items-center gap-3 mt-4">
            <button type="button" @click="showPreview = !showPreview"
              class="btn-secondary text-sm">
              {{ showPreview ? '✏️ تعديل' : '👁 معاينة' }}
            </button>
            <button type="submit" :disabled="form.processing" class="btn-primary">
              💾 حفظ السياسة
            </button>
            <span v-if="saved" class="text-emerald-600 text-sm font-medium">✅ تم الحفظ!</span>
          </div>
        </form>

        <!-- Markdown Preview -->
        <div v-if="showPreview"
          class="mt-6 p-5 bg-slate-50 border border-slate-200 rounded-xl prose prose-slate max-w-none text-sm leading-7"
          dir="rtl"
          v-html="rendered">
        </div>
      </div>

      <!-- Character count -->
      <p class="text-slate-400 text-xs mt-2 text-left">
        {{ content.length }} حرف
      </p>
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
    .replace(/^## (.+)$/gm, '<h2 class="text-base font-bold text-slate-800 mt-4 mb-1">$1</h2>')
    .replace(/^# (.+)$/gm, '<h1 class="text-lg font-bold text-slate-800 mt-4 mb-1">$1</h1>')
    .replace(/^- (.+)$/gm, '<li class="text-slate-600">$1</li>')
    .replace(/^---$/gm, '<hr class="my-3 border-slate-200">')
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
