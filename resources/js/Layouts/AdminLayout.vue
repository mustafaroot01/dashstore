<template>
  <div class="h-screen overflow-hidden flex bg-slate-50">

    <!-- Mobile Overlay -->
    <div v-show="sidebarOpen" @click="sidebarOpen = false" 
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden"></div>

    <!-- ═══ Sidebar ═══════════════════════════════════════════ -->
    <aside class="sidebar flex flex-col transition-transform duration-300 lg:translate-x-0 z-50"
           :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full'">
      <!-- Logo -->
      <div class="flex items-center justify-between px-6 py-5 border-b border-slate-700/50">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl overflow-hidden bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shrink-0">
            <template v-if="$page.props.app_settings?.dashboard_logo">
              <img :src="`/storage/${$page.props.app_settings.dashboard_logo}`" class="w-full h-full object-cover bg-white" />
            </template>
            <template v-else>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-5 h-5">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15H9V8h2v9zm4 0h-2V8h2v9z"/>
              </svg>
            </template>
          </div>
          <div>
            <p class="text-white font-bold text-sm">{{ $page.props.app_settings?.dashboard_name || 'أمواج ديالى' }}</p>
            <p class="text-slate-400 text-xs">لوحة التحكم</p>
          </div>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <!-- Removed flex-1 so it does not force the logout button to the absolute bottom if nav is short -->
      <nav class="sidebar-nav py-4 space-y-1 overflow-y-auto mb-auto">
        <div class="px-4 mb-2">
          <p class="text-slate-500 text-[11px] font-bold uppercase tracking-widest mb-1">القائمة الرئيسية</p>
        </div>

        <SidebarLink :href="route('admin.dashboard')" icon="home" label="الرئيسية" />
        <SidebarLink v-if="hasPermission('manage_orders')" :href="route('admin.orders.index')" icon="orders" label="الطلبات" />
        <SidebarLink v-if="hasPermission('manage_products')" :href="route('admin.products.index')" icon="products" label="المنتجات" />
        <SidebarLink v-if="hasPermission('manage_products')" :href="route('admin.categories.index')" icon="categories" label="الأقسام" />
        <SidebarLink v-if="hasPermission('manage_settings')" :href="route('admin.governorates.index')" icon="map" label="المحافظات" />
        <SidebarLink v-if="hasPermission('manage_settings')" :href="route('admin.districts.index')" icon="map" label="الأقضية" />
        <SidebarLink v-if="hasPermission('manage_users')" :href="route('admin.users.index')" icon="users" label="الزبائن" />
        <SidebarLink v-if="hasPermission('manage_content')" :href="route('admin.sliders.index')" icon="sliders" label="السلايدات" />
        <SidebarLink v-if="hasPermission('manage_financial')" :href="route('admin.coupons.index')" icon="coupons" label="الكوبونات" />

        <div v-if="hasPermission('manage_admins_roles')" class="px-3 mt-4 mb-2">
          <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider px-2 mb-1">المشرفين والصلاحيات</p>
        </div>

        <SidebarLink v-if="hasPermission('manage_admins_roles')" :href="route('admin.admins.index')" icon="users" label="حسابات المشرفين" />
        <SidebarLink v-if="hasPermission('manage_admins_roles')" :href="route('admin.roles.index')" icon="shield-exclamation" label="صلاحيات الأدوار" />

        <div v-if="hasPermission('manage_settings') || hasPermission('manage_content')" class="px-3 mt-4 mb-2">
          <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider px-2 mb-1">إدارة النظام</p>
        </div>

        <SidebarLink v-if="hasPermission('manage_settings')" :href="route('admin.settings.index')" icon="settings" label="الإعدادات العامة" />
        <SidebarLink v-if="hasPermission('manage_content')" :href="route('admin.privacy-policy.index')" icon="document" label="سياسة الخصوصية" />
        
        <div v-if="hasPermission('manage_settings') || hasPermission('manage_inventory')" class="px-3 mt-4 mb-2">
          <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider px-2 mb-1">التقارير والمراقبة</p>
        </div>
        
        <SidebarLink v-if="hasPermission('manage_inventory')" :href="route('admin.inventory.index')" icon="document-text" label="الجرد والتقارير" />
        <SidebarLink v-if="hasPermission('manage_settings')" :href="route('admin.activity-log.index')" icon="log" label="سجل النشاطات" />
        <SidebarLink v-if="hasPermission('manage_settings')" :href="route('admin.error-log.index')" icon="shield-exclamation" label="سجلات الأخطاء" />
        <SidebarLink v-if="hasPermission('manage_settings')" :href="route('admin.api-docs.index')" icon="code-bracket" label="الواجهة البرمجية (API)" />
      </nav>

      <!-- Logout -->
      <div class="p-4 border-t border-slate-700/50 mt-4">
        <div class="flex items-center gap-3 mb-3 px-2">
          <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold">
            {{ authAdmin?.name?.charAt(0) || 'أ' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-white text-sm font-medium truncate">{{ authAdmin?.name || 'مدير النظام' }}</p>
            <p class="text-slate-400 text-xs truncate" dir="ltr">{{ authAdmin?.email || 'admin@amwaj.iq' }}</p>
          </div>
        </div>
        <form @submit.prevent="logout" class="w-full">
          <button type="submit"
            class="w-full flex items-center gap-2 px-3 py-2 text-slate-400 hover:text-red-400
                   hover:bg-red-500/10 rounded-lg transition-all text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            تسجيل الخروج
          </button>
        </form>
      </div>
    </aside>

    <!-- ═══ Main ════════════════════════════════════════════════ -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto w-full lg:pr-[260px]">
      <!-- Topbar -->
      <header class="topbar sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
          <!-- Mobile Menu Toggle -->
          <button @click="sidebarOpen = true" class="lg:hidden p-2 -mr-2 text-slate-500 hover:text-slate-700 rounded-lg">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          <h1 class="text-lg font-bold text-slate-800">{{ title }}</h1>
        </div>
        <div class="flex items-center gap-2">
          <!-- Flash Messages -->
          <div class="flex gap-2 relative">
            <Transition name="slide-fade">
              <div v-if="flash.success"
                class="flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-100
                       px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ flash.success }}
              </div>
            </Transition>
            
            <Transition name="slide-fade">
              <div v-if="flash.error"
                class="flex items-center gap-2 bg-red-50 text-red-700 border border-red-100
                       px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                {{ flash.error }}
              </div>
            </Transition>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="page-content flex-1 p-4 lg:p-6 overflow-x-hidden">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import SidebarLink from '@/Components/SidebarLink.vue';

const props  = defineProps({ title: { type: String, default: '' } });
const page   = usePage();
const flash  = computed(() => page.props.flash ?? {});
const authAdmin = computed(() => page.props.auth?.admin);
const sidebarOpen = ref(false);

// Helper function to check permissions based on the role injected from Inertia
function hasPermission(permission) {
  if (!authAdmin.value) return false;
  
  // Super admin (no role assigned) has all permissions
  if (!authAdmin.value.role_id) return true;
  
  // If the admin has a role, check its permissions array
  const role = authAdmin.value.role;
  if (!role || !role.permissions) return false;
  
  return role.permissions.includes(permission);
}

function logout() {
  router.post(route('admin.logout'));
}
</script>

<style scoped>
.slide-fade-enter-active, .slide-fade-leave-active { transition: all .3s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
