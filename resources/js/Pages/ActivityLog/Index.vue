<template>
  <AdminLayout title="سجل النشاطات">
    <div class="flex flex-wrap items-center gap-3 mb-6">
      <input v-model="f.search" type="text" placeholder="بحث في النشاطات..."
        class="form-input w-64" @input="doFilter" />
      <input v-model="f.date" type="date" class="form-input w-44" @change="doFilter" />
    </div>

    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>النشاط</th>
              <th>المشرف</th>
              <th>الآيبي</th>
              <th>الجهاز</th>
              <th>المتصفح</th>
              <th>التاريخ والوقت</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(log, index) in logs.data" :key="log.id">
              <td class="text-slate-400 text-xs font-mono text-center">
                {{ (logs.current_page - 1) * logs.per_page + index + 1 }}
              </td>
              <td class="font-medium text-slate-700">{{ log.action }}</td>
              <td class="text-slate-600">{{ log.admin?.name }}</td>
              <td dir="ltr" class="font-mono text-xs text-slate-500">{{ log.ip_address }}</td>
              <td class="text-slate-500 text-sm">{{ log.device ?? '—' }}</td>
              <td class="text-slate-500 text-sm">{{ log.browser ?? '—' }}</td>
              <td class="text-slate-400 text-xs">{{ new Date(log.created_at).toLocaleString('ar') }}</td>
            </tr>
          </tbody>
        </table>
        <p v-if="!logs.data.length" class="text-center text-slate-400 text-sm py-8">لا توجد سجلات</p>
      </div>

      <!-- Pagination -->
      <div v-if="logs.last_page > 1" class="flex justify-between items-center px-4 py-3 border-t">
        <span class="text-slate-500 text-sm">{{ logs.total }} سجل</span>
        <div class="flex gap-1">
          <Link v-for="page in logs.links" :key="page.label" :href="page.url ?? '#'"
            class="px-3 py-1.5 rounded-lg text-sm"
            :class="page.active ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600'">
            <span v-html="page.label" />
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ logs: Object, filters: Object });
const f = reactive({ search: props.filters?.search ?? '', date: props.filters?.date ?? '' });

let timer;
function doFilter() {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(route('admin.activity-log.index'), f, { preserveState: true });
  }, 400);
}
</script>
