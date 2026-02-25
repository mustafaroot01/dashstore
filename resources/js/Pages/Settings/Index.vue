<template>
  <AdminLayout title="الإعدادات">
    <div class="max-w-2xl">
      <form @submit.prevent="submit">

        <!-- OTP Settings -->
        <div class="card mb-4">
          <h3 class="font-semibold text-slate-800 mb-4 text-base">إعدادات OTP</h3>
          <div class="form-group">
            <label class="form-label">قناة الإرسال</label>
            <div class="flex gap-4">
              <label v-for="ch in ['whatsapp', 'sms']" :key="ch"
                class="flex items-center gap-2 cursor-pointer p-3 border rounded-xl flex-1 transition"
                :class="form.otp_channel === ch ? 'border-primary-400 bg-primary-50' : 'border-slate-200'">
                <input type="radio" v-model="form.otp_channel" :value="ch" class="text-primary-600" />
                <div>
                  <p class="font-medium text-sm text-slate-800">{{ ch === 'whatsapp' ? '📱 واتساب' : '💬 SMS' }}</p>
                  <p class="text-xs text-slate-400">{{ ch === 'whatsapp' ? 'أسرع وأوثوق' : 'رسائل نصية' }}</p>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- Invoice Settings -->
        <div class="card mb-4">
          <h3 class="font-semibold text-slate-800 mb-4 text-base">إعدادات الفاتورة</h3>
          <div class="space-y-4">
            <!-- Logo -->
            <div class="form-group">
              <label class="form-label">شعار الشركة</label>
              <div class="flex items-center gap-4">
                <img v-if="logoPreview" :src="logoPreview" class="w-16 h-16 object-contain rounded-xl border" />
                <button type="button" @click="$refs.logo.click()" class="btn-secondary text-sm">
                  {{ logoPreview ? 'تغيير الشعار' : 'رفع الشعار' }}
                </button>
                <input ref="logo" type="file" accept="image/*" class="hidden" @change="onLogo" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="form-group">
                <label class="form-label">اسم الشركة</label>
                <input v-model="form.invoice_name" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">هاتف الشركة</label>
                <input v-model="form.invoice_phone" class="form-input" dir="ltr" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">عنوان الشركة</label>
              <input v-model="form.invoice_address" class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">نص ختامي (اختياري)</label>
              <textarea v-model="form.invoice_footer" class="form-textarea" rows="2"></textarea>
            </div>
          </div>
        </div>

        <button type="submit" :disabled="form.processing" class="btn-primary">
          حفظ الإعدادات
        </button>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ settings: Object });

const logoPreview = ref(props.settings.invoice_logo ? '/storage/' + props.settings.invoice_logo : null);

const form = useForm({
  otp_channel:     props.settings.otp_channel ?? 'whatsapp',
  invoice_name:    props.settings.invoice_name ?? '',
  invoice_phone:   props.settings.invoice_phone ?? '',
  invoice_address: props.settings.invoice_address ?? '',
  invoice_footer:  props.settings.invoice_footer ?? '',
  invoice_logo:    null,
});

function onLogo(e) {
  form.invoice_logo = e.target.files[0];
  logoPreview.value = URL.createObjectURL(form.invoice_logo);
}

function submit() {
  form.post(route('admin.settings.update'));
}
</script>
