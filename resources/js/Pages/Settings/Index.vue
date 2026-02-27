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

        <!-- Dashboard Branding Settings -->
        <h3 class="font-bold text-slate-800 text-lg mb-4 mt-8">هوية لوحة التحكم (البراند)</h3>
        <div class="card mb-4 border-t-4 border-t-indigo-500 rounded-lg shadow-sm">
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
              <div class="form-group border-r-2 border-slate-200 pr-3">
                <label class="form-label text-slate-700 font-medium tracking-wide">اسم المنصة (لوحة التحكم)</label>
                <input v-model="form.dashboard_name" class="form-input bg-slate-50/50 hover:bg-white focus:bg-white transition" placeholder="مثال: لوحة أمواج" />
                <p class="text-xs text-slate-500 mt-1">هذا الاسم سيظهر في أعلى القائمة الجانبية (Sidebar).</p>
              </div>

              <div class="form-group border-r-2 border-slate-200 pr-3">
                <label class="form-label text-slate-700 font-medium tracking-wide">شعار لوحة التحكم (صغير)</label>
                <div class="flex items-center gap-4 mt-2">
                  <div class="w-12 h-12 bg-slate-100 rounded-lg border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                    <img v-if="dashboardLogoPreview" :src="dashboardLogoPreview" class="w-full h-full object-contain p-1" />
                    <svg v-else class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                  </div>
                  <div class="flex-1">
                    <input type="file" ref="dashboardLogo" accept="image/*" class="hidden" @change="onDashboardLogo" />
                    <button type="button" @click="$refs.dashboardLogo.click()" class="btn-secondary text-xs px-3 py-1.5 focus:ring-0">اختيار صورة</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Invoice Settings -->
        <h3 class="font-bold text-slate-800 text-lg mb-4 mt-8">إعدادات الفاتورة</h3>
        <div class="card mb-4 border-t-4 border-t-primary-500 rounded-lg shadow-sm">
          <div class="space-y-6">
            <!-- Logo Section -->
            <div class="form-group bg-slate-50 p-6 rounded-xl border border-slate-100 flex flex-col items-center justify-center text-center">
              <label class="block text-slate-700 font-semibold mb-3">شعار الشركة (اللوگو)</label>
              
              <div class="relative group cursor-pointer" @click="$refs.logo.click()">
                <div class="w-32 h-32 bg-white rounded-2xl border-2 border-dashed border-primary-200 flex flex-col items-center justify-center overflow-hidden hover:border-primary-400 transition-colors shadow-sm">
                  <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-contain p-2" />
                  <div v-else class="text-slate-400 flex flex-col items-center">
                    <svg class="w-8 h-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    <span class="text-xs font-medium">اضغط لرفع الشعار</span>
                  </div>
                  <!-- Overlay -->
                  <div v-if="logoPreview" class="absolute inset-0 bg-primary-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="text-white text-sm font-semibold">تغيير الشعار</span>
                  </div>
                </div>
              </div>
              <input ref="logo" type="file" accept="image/*" class="hidden" @change="onLogo" />
              <p class="text-xs text-slate-500 mt-3 max-w-xs mx-auto">يفضل أن يكون بخلفية شفافة (PNG) وبأبعاد متساوية.</p>
            </div>

            <!-- Text Fields Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div class="form-group border-r-2 border-slate-200 pr-3">
                <label class="form-label text-slate-700 font-medium tracking-wide">اسم الشركة</label>
                <input v-model="form.invoice_name" class="form-input bg-slate-50/50 hover:bg-white focus:bg-white transition" placeholder="مثال: شركة أمواج ديالى" />
              </div>
              
              <div class="form-group border-r-2 border-slate-200 pr-3">
                <label class="form-label text-slate-700 font-medium tracking-wide">رقم الهاتف الشائع</label>
                <input v-model="form.invoice_phone" class="form-input bg-slate-50/50 hover:bg-white focus:bg-white transition" dir="ltr" placeholder="مثال: 0770 000 0000" />
              </div>

              <div class="form-group border-r-2 border-slate-200 pr-3 md:col-span-2">
                <label class="form-label text-slate-700 font-medium tracking-wide">رمز الفاتورة المبدئي (Invoice Prefix)</label>
                <input v-model="form.invoice_prefix" class="form-input bg-slate-50/50 hover:bg-white focus:bg-white transition" dir="ltr" placeholder="مثال: AW-" />
                <p class="text-[11px] text-slate-500 mt-1">ستبدأ كل الحجوزات والطلبات الجديدة بهذا الرمز (مثل AW-00001).</p>
              </div>
              
              <div class="form-group border-r-2 border-slate-200 pr-3 md:col-span-2">
                <label class="form-label text-slate-700 font-medium tracking-wide">العنوان الرسمي / المقر الرئيسي</label>
                <input v-model="form.invoice_address" class="form-input bg-slate-50/50 hover:bg-white focus:bg-white transition" placeholder="مثال: ديالى - بعقوبة - شارع المحافظة" />
              </div>

              <div class="form-group border-r-2 border-slate-200 pr-3 md:col-span-2">
                <label class="form-label text-slate-700 font-medium tracking-wide">رسالة ختامية / شروط الاسترجاع (أسفل الفاتورة)</label>
                <textarea v-model="form.invoice_footer" class="form-textarea bg-slate-50/50 hover:bg-white focus:bg-white transition resize-none" rows="2" placeholder="مثال: البضاعة المباعة لا ترد ولا تستبدل بعد 3 أيام... نتمنى لكم تجربة سعيدة."></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Telegram Notifications -->
        <h3 class="font-bold text-slate-800 text-lg mb-4 mt-8 flex items-center gap-2">
          <i class="fab fa-telegram text-[#0088cc]"></i>
          إشعارات تلغرام
        </h3>
        <div class="card mb-8 border-t-4 border-t-[#0088cc] rounded-lg shadow-sm">
          <div class="space-y-6">
            <p class="text-sm text-slate-600 mb-2">
              استلم إشعاراً لكل طلب جديد مباشرةً على محادثتك في التلغرام. 
              قم بإنشاء بوت عبر 
              <a href="https://t.me/BotFather" target="_blank" class="text-[#0088cc] font-bold hover:underline" dir="ltr">@BotFather</a> 
              ثم أدخل تفاصيله هنا.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div class="form-group border-r-2 border-slate-200 pr-3">
                <label class="form-label text-slate-700 font-medium tracking-wide">رمز البوت (Bot Token)</label>
                <input v-model="form.telegram_bot_token" class="form-input bg-slate-50/50 hover:bg-white focus:bg-white transition" dir="ltr" placeholder="مثال: 123456:ABC-DEF1234ghIkl-zyx5" />
              </div>
              <div class="form-group border-r-2 border-slate-200 pr-3">
                <label class="form-label text-slate-700 font-medium tracking-wide">رقم المحادثة (Chat ID)</label>
                <div class="flex gap-2">
                  <input v-model="form.telegram_chat_id" class="form-input bg-slate-50/50 hover:bg-white focus:bg-white transition flex-1" dir="ltr" placeholder="مثال: 123456789 (أو للگروب -123456)" />
                  <button type="button" @click="testTelegramMessage" :disabled="isTestingTelegram || !form.telegram_bot_token || !form.telegram_chat_id"
                    class="btn-success text-xs px-3 py-2 whitespace-nowrap flex items-center justify-center gap-1.5 focus:ring-0">
                    <svg v-if="isTestingTelegram" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <i v-else class="fas fa-paper-plane"></i>
                    تجربة
                  </button>
                </div>
                <!-- Test Result Message -->
                <p v-if="testTelegramMessageText" class="mt-2 text-xs font-bold transition-all" :class="testTelegramSuccess ? 'text-emerald-600' : 'text-red-500'">
                  {{ testTelegramMessageText }}
                </p>
              </div>
            </div>
            
            <div class="mt-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
              <div class="flex items-start justify-between">
                <div>
                  <h4 class="font-bold text-slate-800 text-sm mb-1">استخراج الـ Chat ID الذكي 🪄</h4>
                  <p class="text-xs text-slate-600 mb-3 max-w-lg leading-relaxed">
                    1. الصق <b>رمز البوت</b> في الحقل أعلاه.<br/>
                    2. أرسل أي رسالة للبوت من حسابك في التلغرام.<br/>
                    3. اضغط <b>استخراج الآي دي</b> (يمكنك ترك حقل اليوزر فارغاً لتبسيط الأمر).
                  </p>
                  <div class="flex flex-wrap items-center gap-2 mb-2 w-full max-w-sm relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold">@</span>
                    <input v-model="searchUsername" type="text" class="form-input text-sm py-2 pl-8 w-full" placeholder="اختياري: يوزر حسابك (مثال: ali_tech)" dir="ltr" />
                  </div>
                </div>
                <!-- Action Button in its own block for better mobile responsiveness if needed, but flex-items-start keeps it top-right -->
                <button type="button" @click="fetchChatId" :disabled="isFetchingId || !form.telegram_bot_token"
                  class="btn-secondary text-xs px-4 py-2 mt-2 flex items-center gap-2 whitespace-nowrap min-w-[140px] justify-center">
                  <svg v-if="isFetchingId" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                  <i v-else class="fas fa-search text-[#0088cc]"></i>
                  {{ isFetchingId ? 'جاري البحث...' : 'استخراج الآي دي' }}
                </button>
              </div>
              <p v-if="fetchMessage" class="mt-2 text-xs font-bold" :class="fetchSuccess ? 'text-emerald-600' : 'text-red-500'">
                {{ fetchMessage }}
              </p>
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
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ settings: Object });

const logoPreview = ref(props.settings.invoice_logo ? '/storage/' + props.settings.invoice_logo : null);
const dashboardLogoPreview = ref(props.settings.dashboard_logo ? '/storage/' + props.settings.dashboard_logo : null);
const isFetchingId = ref(false);
const fetchMessage = ref('');
const fetchSuccess = ref(false);
const searchUsername = ref('');

// Test message state
const isTestingTelegram = ref(false);
const testTelegramMessageText = ref('');
const testTelegramSuccess = ref(false);

async function testTelegramMessage() {
  if (!form.telegram_bot_token || !form.telegram_chat_id) return;
  
  isTestingTelegram.value = true;
  testTelegramMessageText.value = '';
  
  try {
    const res = await axios.post(route('admin.settings.telegram.test'), {
      telegram_bot_token: form.telegram_bot_token,
      telegram_chat_id: form.telegram_chat_id,
    });
    
    testTelegramSuccess.value = res.data.success;
    testTelegramMessageText.value = res.data.message;
  } catch (err) {
    testTelegramSuccess.value = false;
    testTelegramMessageText.value = err.response?.data?.message || 'تعذر الاتصال بالخادم.';
  } finally {
    isTestingTelegram.value = false;
  }
}

const form = useForm({
  otp_channel:     props.settings.otp_channel ?? 'whatsapp',
  invoice_name:    props.settings.invoice_name ?? '',
  invoice_phone:   props.settings.invoice_phone ?? '',
  invoice_address: props.settings.invoice_address ?? '',
  invoice_footer:  props.settings.invoice_footer ?? '',
  invoice_logo:    null,
  invoice_prefix:  props.settings.invoice_prefix ?? 'AW-',
  dashboard_name:      props.settings.dashboard_name ?? 'أمواج ديالى',
  dashboard_logo:      null,
  telegram_bot_token: props.settings.telegram_bot_token ?? '',
  telegram_chat_id:   props.settings.telegram_chat_id ?? '',
});

async function fetchChatId() {
  if (!form.telegram_bot_token) {
    fetchMessage.value = 'الرجاء إدخال رمز البوت (Token) أولاً.';
    return;
  }
  if (!searchUsername.value) {
    fetchMessage.value = 'الرجاء إدخال معرّف التلغرام (اليوزر).';
    return;
  }
  
  isFetchingId.value = true;
  fetchMessage.value = '';
  fetchSuccess.value = false;

  try {
    const res = await fetch(`https://api.telegram.org/bot${form.telegram_bot_token}/getUpdates`);
    const data = await res.json();
    
    if (data.ok && data.result.length > 0) {
      // Find the message from the specific username (case-insensitive)
      const targetUser = searchUsername.value.trim().replace('@', '').toLowerCase();
      
      // Iterate backwards to get the most recent message from this user
      let foundChatId = null;
      for (let i = data.result.length - 1; i >= 0; i--) {
        const update = data.result[i];
        const fromUser = update.message?.from?.username || update.my_chat_member?.from?.username;
        
        if (fromUser && fromUser.toLowerCase() === targetUser) {
          foundChatId = update.message?.chat?.id || update.my_chat_member?.chat?.id;
          break;
        }
      }
      
      if (foundChatId) {
        form.telegram_chat_id = foundChatId.toString();
        fetchSuccess.value = true;
        fetchMessage.value = `تم العثور على المعرف بنجاح (${foundChatId})! لا تنسَ حفظ الإعدادات.`;
      } else {
        fetchMessage.value = `لم نتمكن من العثور على حساب باليوزر (@${targetUser}). هل تأكدت من إرسال رسالة للبوت؟`;
      }
    } else {
      fetchMessage.value = 'لا توجد أي رسائل جديدة في البوت. قم بإرسال رسالة للبوت وحاول مجدداً.';
    }
  } catch (err) {
    fetchMessage.value = 'تأكد من صحة رمز البوت (Token) أو حاول مرة أخرى.';
  } finally {
    isFetchingId.value = false;
  }
}

function onLogo(e) {
  form.invoice_logo = e.target.files[0];
  logoPreview.value = URL.createObjectURL(form.invoice_logo);
}

function onDashboardLogo(e) {
  form.dashboard_logo = e.target.files[0];
  dashboardLogoPreview.value = URL.createObjectURL(form.dashboard_logo);
}

function submit() {
  form.post(route('admin.settings.update'));
}
</script>
