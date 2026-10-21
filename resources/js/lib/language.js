import { ref, watchEffect } from 'vue';

const translations = {
  // App / Sidebar / Nav
  dashboard: { en: "Dashboard", bn: "ড্যাশবোর্ড" },
  dashboardReport: { en: "Dashboard Report", bn: "ড্যাশবোর্ড রিপোর্ট" },
  reports: { en: "Reports", bn: "রিপোর্ট" },
  orders: { en: "Orders", bn: "অর্ডার" },
  customers: { en: "Customers", bn: "কাস্টমার" },
  customerHistory: { en: "Customer History", bn: "কাস্টমার হিস্ট্রি" },
  staffManagement: { en: "Staff Management", bn: "স্টাফ ম্যানেজমেন্ট" },
  staff: { en: "Staff", bn: "স্টাফ" },
  staffSummary: { en: "Staff Summary", bn: "স্টাফ সারাংশ" },
  history: { en: "History", bn: "হিস্ট্রি" },
  settings: { en: "Settings", bn: "সেটিংস" },
  menu: { en: "Menu", bn: "মেনু" },
  airmanOrders: { en: "Airman Orders", bn: "এয়ারম্যান অর্ডার" },
  airmanShort: { en: "Airman", bn: "এয়ারম্যান" },
  oneRaymond: { en: "One Raymond", bn: "ওয়ান রেমন্ড" },
  tailorPro: { en: "TailorPro", bn: "টেইলরপ্রো" },

  // Dashboard
  businessOverview: { en: "Business overview at a glance", bn: "ব্যবসার সামগ্রিক অবস্থা" },
  totalOrders: { en: "Total Orders", bn: "মোট অর্ডার" },
  todaysOrders: { en: "Today's Orders", bn: "আজকের অর্ডার" },
  monthlyOrders: { en: "Monthly Orders", bn: "মাসিক অর্ডার" },
  totalRevenue: { en: "Total Revenue", bn: "মোট আয়" },
  todaysRevenue: { en: "Today's Revenue", bn: "আজকের আয়" },
  monthlyRevenue: { en: "Monthly Revenue", bn: "মাসিক আয়" },
  pendingDue: { en: "Pending Due", bn: "বকেয়া" },
  deliveredDue: { en: "Delivered Due", bn: "ডেলিভারি বকেয়া" },
  upcomingDeliveries: { en: "Upcoming Deliveries", bn: "আসন্ন ডেলিভারি" },
  totalCustomers: { en: "Total Customers", bn: "মোট কাস্টমার" },
  ordersAndRevenue: { en: "Orders & Revenue", bn: "অর্ডার ও আয়" },
  noDataYet: { en: "No data yet. Create some orders to see the chart.", bn: "এখনও কোনো ডেটা নেই। চার্ট দেখতে অর্ডার তৈরি করুন।" },
  noUpcomingDeliveries: { en: "No upcoming deliveries", bn: "আসন্ন কোনো ডেলিভারি নেই" },
  revenue: { en: "Revenue", bn: "আয়" },
  ordersSummary: { en: "Orders Summary", bn: "অর্ডার সারাংশ" },
  revenueSummary: { en: "Revenue Summary", bn: "আয় সারাংশ" },
  quickStats: { en: "Quick Stats", bn: "দ্রুত পরিসংখ্যান" },
  todayOverview: { en: "Today's Overview", bn: "আজকের ওভারভিউ" },
  monthlyOverview: { en: "Monthly Overview", bn: "মাসিক ওভারভিউ" },
  weeklyOverview: { en: "Weekly Overview", bn: "সাপ্তাহিক ওভারভিউ" },
  weeklyOrders: { en: "Weekly Orders", bn: "সাপ্তাহিক অর্ডার" },
  weeklySales: { en: "Weekly Sales", bn: "সাপ্তাহিক বিক্রি" },
  weeklyCollection: { en: "Weekly Collection", bn: "সাপ্তাহিক আদায়" },
  weeklyDue: { en: "Weekly Due", bn: "সাপ্তাহিক বকেয়া" },
  totalOverview: { en: "Total Overview", bn: "সামগ্রিক ওভারভিউ" },
  todaysSales: { en: "Today's Sales", bn: "আজকের বিক্রি" },
  todaysCollection: { en: "Today's Collection", bn: "আজকের আদায়" },
  todaysDue: { en: "Today's Due", bn: "আজকের বকেয়া" },
  monthlySales: { en: "Monthly Sales", bn: "মাসিক বিক্রি" },
  monthlyCollection: { en: "Monthly Collection", bn: "মাসিক আদায়" },
  monthlyDue: { en: "Monthly Due", bn: "মাসিক বকেয়া" },
  totalSales: { en: "Total Sales", bn: "মোট বিক্রি" },
  totalCollection: { en: "Total Collection", bn: "মোট আদায়" },
  totalDue: { en: "Total Due", bn: "মোট বকেয়া" },
  deliveredOrders: { en: "Delivered Orders", bn: "ডেলিভারড অর্ডার" },
  analytics: { en: "Analytics", bn: "বিশ্লেষণ" },
  recentOrders: { en: "Recent Orders", bn: "সাম্প্রতিক অর্ডার" },
  collection: { en: "Collection", bn: "আদায়" },
  sales: { en: "Sales", bn: "বিক্রি" },

  // Orders
  ordersTitle: { en: "Orders", bn: "অর্ডার" },
  ordersSubtitle: { en: "Manage and track tailoring orders", bn: "টেইলারিং অর্ডার পরিচালনা ও ট্র্যাক করুন" },
  airmanOrdersTitle: { en: "Airman Orders", bn: "এয়ারম্যান অর্ডার" },
  airmanOrdersSubtitle: { en: "Manage Airman uniform cutting and delivery", bn: "এয়ারম্যান ইউনিফর্ম কাটিং ও ডেলিভারি পরিচালনা" },
  newAirmanOrder: { en: "New Airman Order", bn: "নতুন এয়ারম্যান অর্ডার" },
  allCuttingSlips: { en: "All Cutting Slips", bn: "সকল কাটিং স্লিপ" },
  exportPDF: { en: "Export PDF", bn: "PDF এক্সপোর্ট" },
  deliveryTitle: { en: "Delivery", bn: "ডেলিভারি" },
  deliverySubtitle: { en: "Search and deliver pending officer orders", bn: "অফিসারদের প্রস্তুতকৃত অর্ডার ডেলিভারি করুন" },
  downloadUpdatedInvoice: { en: "Download Updated Invoice?", bn: "আপডেটেড ইনভয়েস ডাউনলোড করবেন?" },
  orderMarkedDeliveredDesc: { en: "The order has been marked as delivered. Would you like to download the updated invoice with final payment details?", bn: "অর্ডারটি সফলভাবে ডেলিভারড হিসেবে চিহ্নিত হয়েছে। চূড়ান্ত পেমেন্ট বিবরণীসহ ইনভয়েস ডাউনলোড করতে চান?" },
  downloadInvoice: { en: "Download Invoice", bn: "ইনভয়েস ডাউনলোড" },
  noSkip: { en: "No, Skip", bn: "না, বাদ দিন" },
  completeDelivery: { en: "Complete Delivery", bn: "ডেলিভারি সম্পন্ন করুন" },
  noPendingDeliveries: { en: "No pending deliveries", bn: "কোনো পেন্ডিং ডেলিভারি নেই" },
  noOrdersMatchingSearch: { en: "No orders match your search", bn: "সার্চের সাথে মিল থাকা কোনো অর্ডার নেই" },
  officerCustomers: { en: "Officer Customers", bn: "অফিসার কাস্টমার" },
  airmanCustomers: { en: "Airman Customers", bn: "এয়ারম্যান কাস্টমার" },
  workerEntries: { en: "Worker Entries", bn: "কর্মী এন্ট্রি" },
  manageOrders: { en: "Manage all tailoring orders", bn: "সকল টেইলরিং অর্ডার পরিচালনা করুন" },
  newOrder: { en: "New Order", bn: "নতুন অর্ডার" },
  editOrder: { en: "Edit Order", bn: "অর্ডার এডিট করুন" },
  createNewOrder: { en: "Create New Order", bn: "নতুন অর্ডার তৈরি করুন" },
  fillOrderDetails: { en: "Fill in the order details below", bn: "নিচে অর্ডারের তথ্য পূরণ করুন" },
  phone: { en: "Phone", bn: "ফোন" },
  customerName: { en: "Customer Name", bn: "কাস্টমারের নাম" },
  bdNumber: { en: "BD Number", bn: "BD নম্বর" },
  orderDate: { en: "Order Date", bn: "অর্ডারের তারিখ" },
  deliveryDate: { en: "Delivery Date", bn: "ডেলিভারি তারিখ" },
  totalAmount: { en: "Total Amount", bn: "মোট পরিমাণ" },
  advance: { en: "Advance", bn: "অগ্রিম" },
  due: { en: "Due", bn: "বকেয়া" },
  discount: { en: "Discount", bn: "ডিসকাউন্ট" },
  optional: { en: "Optional", bn: "ঐচ্ছিক" },
  items: { en: "Items", bn: "আইটেম" },
  addItem: { en: "Add Item", bn: "আইটেম যোগ করুন" },
  measurementNotes: { en: "Measurement Notes", bn: "মেজারমেন্ট নোট" },
  extraNotes: { en: "Extra Notes", bn: "অতিরিক্ত নোট" },
  runningYear: { en: "Running Year", bn: "অর্থবছর" },
  cancel: { en: "Cancel", bn: "বাতিল" },
  saveOrder: { en: "Save Order", bn: "অর্ডার সেভ করুন" },
  updateOrder: { en: "Update Order", bn: "অর্ডার আপডেট করুন" },
  all: { en: "All", bn: "সব" },
  pending: { en: "Pending", bn: "পেন্ডিং" },
  delivered: { en: "Delivered", bn: "ডেলিভারড" },
  duePayment: { en: "Due", bn: "বকেয়া" },
  noOrdersFound: { en: "No orders found. Create your first order!", bn: "কোনো অর্ডার পাওয়া যায়নি। প্রথম অর্ডার তৈরি করুন!" },
  searchOrderPlaceholder: { en: "Search by name, phone, BD number, order #...", bn: "নাম, ফোন, BD নম্বর, অর্ডার # দিয়ে সার্চ করুন..." },
  total: { en: "Total", bn: "মোট" },
  unknown: { en: "Unknown", bn: "অজানা" },
  shirt: { en: "Shirt", bn: "শার্ট" },
  pant: { en: "Pant", bn: "প্যান্ট" },
  panjabi: { en: "Panjabi", bn: "পাঞ্জাবি" },
  blazer: { en: "Blazer", bn: "ব্লেজার" },
  custom: { en: "Custom", bn: "কাস্টম" },
  itemNameOptional: { en: "Item name (optional)", bn: "আইটেমের নাম (ঐচ্ছিক)" },
  itemNotes: { en: "Measurement Notes", bn: "মেজারমেন্ট নোট" },
  typeCustomerName: { en: "Type customer name...", bn: "কাস্টমারের নাম লিখুন..." },

  // Customers
  manageCustomers: { en: "Manage all customers", bn: "সকল কাস্টমার পরিচালনা করুন" },
  addCustomer: { en: "Add Customer", bn: "কাস্টমার যোগ করুন" },
  editCustomer: { en: "Edit Customer", bn: "কাস্টমার এডিট করুন" },
  fillCustomerDetails: { en: "Fill in the customer details below", bn: "নিচে কাস্টমারের তথ্য পূরণ করুন" },
  name: { en: "Name", bn: "নাম" },
  bdNumberUniqueId: { en: "BD Number (Unique ID)", bn: "BD নম্বর (Unique ID)" },
  update: { en: "Update", bn: "আপডেট" },
  searchCustomerPlaceholder: { en: "Search name, phone, or BD number...", bn: "নাম, ফোন বা BD নম্বর দিয়ে সার্চ করুন..." },
  noCustomersFound: { en: "No customers found.", bn: "কোনো কাস্টমার পাওয়া যায়নি।" },
  spent: { en: "Spent", bn: "খরচ" },
  delete: { en: "Delete", bn: "ডিলিট" },
  deleteCustomer: { en: "Delete Customer", bn: "কাস্টমার ডিলিট" },
  deleteCustomerConfirm: { en: "Are you sure you want to delete this customer? This action cannot be undone.", bn: "আপনি কি নিশ্চিত এই কাস্টমার ডিলিট করতে চান? এটি আর ফেরত আনা যাবে না।" },
  deleteOrder: { en: "Delete Order", bn: "অর্ডার ডিলিট" },
  deleteOrderConfirm: { en: "Are you sure you want to delete this order? This action cannot be undone.", bn: "আপনি কি নিশ্চিত এই অর্ডার ডিলিট করতে চান? এটি আর ফেরত আনা যাবে না।" },

  // Customer History
  viewHistory: { en: "View complete history for a customer", bn: "কাস্টমারের সম্পূর্ণ হিস্ট্রি দেখুন" },
  searchHistoryPlaceholder: { en: "Search by name, phone, BD number...", bn: "নাম, ফোন, BD নম্বর দিয়ে সার্চ করুন..." },
  orderHistory: { en: "Order History", bn: "অর্ডার হিস্ট্রি" },
  noOrdersFoundHistory: { en: "No orders found.", bn: "কোনো অর্ডার পাওয়া যায়নি।" },
  searchCustomerAbove: { en: "Search for a customer above to view their history", bn: "হিস্ট্রি দেখতে উপরে কাস্টমার সার্চ করুন" },

  // Settings
  manageSettings: { en: "Manage your profile, security & team access", bn: "প্রোফাইল, সিকিউরিটি ও টিম অ্যাক্সেস পরিচালনা করুন" },
  profile: { en: "Profile", bn: "প্রোফাইল" },
  security: { en: "Security", bn: "সিকিউরিটি" },
  access: { en: "Access", bn: "অ্যাক্সেস" },
  profileInfo: { en: "Profile Information", bn: "প্রোফাইল তথ্য" },
  updateDisplayName: { en: "Update your display name and profile details", bn: "আপনার ডিসপ্লে নাম ও প্রোফাইল আপডেট করুন" },
  email: { en: "Email", bn: "ইমেইল" },
  displayName: { en: "Display Name", bn: "ডিসপ্লে নাম" },
  yourName: { en: "Your name", bn: "আপনার নাম" },
  saveChanges: { en: "Save Changes", bn: "সেভ করুন" },
  changePassword: { en: "Change Password", bn: "পাসওয়ার্ড পরিবর্তন করুন" },
  updatePassword: { en: "Update your account password", bn: "একাউন্টের পাসওয়ার্ড আপডেট করুন" },
  newPassword: { en: "New Password", bn: "নতুন পাসওয়ার্ড" },
  confirmPassword: { en: "Confirm Password", bn: "পাসওয়ার্ড নিশ্চিত করুন" },
  enterNewPassword: { en: "Enter new password", bn: "নতুন পাসওয়ার্ড লিখুন" },
  confirmNewPassword: { en: "Confirm new password", bn: "পাসওয়ার্ড নিশ্চিত করুন" },
  inviteUser: { en: "Invite User", bn: "ইউজার ইনভাইট করুন" },
  sendInvitation: { en: "Send an invitation to grant access", bn: "অ্যাক্সেস দিতে ইনভাইটেশন পাঠান" },
  emailAddress: { en: "Email address", bn: "ইমেইল অ্যাড্রেস" },
  invite: { en: "Invite", bn: "ইনভাইট" },
  teamMembers: { en: "Team Members", bn: "টিম মেম্বার" },
  userManagement: { en: "User Management", bn: "ইউজার ব্যবস্থাপনা" },
  manageStaffRolesDesc: { en: "Manage staff roles and dashboard access", bn: "স্টাফ রোল ও ড্যাশবোর্ড অ্যাক্সেস পরিচালনা করুন" },
  manageUserAccess: { en: "Manage user access and roles", bn: "ইউজার অ্যাক্সেস ও রোল পরিচালনা করুন" },
  addUser: { en: "Add User", bn: "ইউজার যোগ করুন" },
  noUsersFound: { en: "No users found", bn: "কোনো ইউজার পাওয়া যায়নি" },
  role: { en: "Role", bn: "রোল" },
  joined: { en: "Joined", bn: "যোগদান" },
  actions: { en: "Actions", bn: "অ্যাকশন" },
  removeUser: { en: "Remove user?", bn: "ইউজার রিমুভ করবেন?" },
  removeUserDesc: { en: "This will permanently delete", bn: "এটি স্থায়ীভাবে ডিলিট করবে" },
  accountAndAccess: { en: "'s account and revoke all access.", bn: " এর একাউন্ট এবং সকল অ্যাক্সেস বাতিল করবে।" },
  remove: { en: "Remove", bn: "রিমুভ" },
  admin: { en: "Admin", bn: "অ্যাডমিন" },
  editor: { en: "Editor", bn: "এডিটর" },
  viewer: { en: "Viewer", bn: "ভিউয়ার" },

  // Login
  signIn: { en: "Sign In", bn: "সাইন ইন" },
  signInDesc: { en: "Sign in to manage your tailoring shop", bn: "আপনার টেইলরিং শপ পরিচালনা করতে সাইন ইন করুন" },
  password: { en: "Password", bn: "পাসওয়ার্ড" },
  forgotPassword: { en: "Forgot password?", bn: "পাসওয়ার্ড ভুলে গেছেন?" },
  sessionExpired: { en: "Your session expired. Please sign in again.", bn: "আপনার সেশন শেষ হয়ে গেছে। আবার সাইন ইন করুন।" },
  checkEmail: { en: "Check Your Email", bn: "আপনার ইমেইল চেক করুন" },
  resetLinkSent: { en: "We've sent a password reset link to", bn: "আমরা পাসওয়ার্ড রিসেট লিংক পাঠিয়েছি" },
  checkInbox: { en: "Please check your inbox and spam folder.", bn: "ইনবক্স ও স্প্যাম ফোল্ডার চেক করুন।" },
  backToLogin: { en: "Back to Login", bn: "লগইনে ফিরে যান" },
  enterEmailForReset: { en: "Enter your email to receive a reset link", bn: "রিসেট লিংক পেতে আপনার ইমেইল লিখুন" },
  sendResetLink: { en: "Send Reset Link", bn: "রিসেট লিংক পাঠান" },

  // Reset Password
  setNewPassword: { en: "Set New Password", bn: "নতুন পাসওয়ার্ড সেট করুন" },
  enterNewPasswordBelow: { en: "Enter your new password below", bn: "নিচে আপনার নতুন পাসওয়ার্ড লিখুন" },
  passwordUpdated: { en: "Password Updated", bn: "পাসওয়ার্ড আপডেট হয়েছে" },
  passwordResetSuccess: { en: "Your password has been reset successfully.", bn: "আপনার পাসওয়ার্ড সফলভাবে রিসেট হয়েছে।" },
  goToDashboard: { en: "Go to Dashboard", bn: "ড্যাশবোর্ডে যান" },
  invalidResetLink: { en: "Invalid or expired reset link. Please request a new one from the login page.", bn: "লিংকটি মেয়াদোত্তীর্ণ বা ভুল। লগইন পেজ থেকে নতুন লিংক নিন।" },

  // Order Detail
  orderDetails: { en: "Order Details", bn: "অর্ডারের বিবরণ" },
  measurements: { en: "Measurements", bn: "মেজারমেন্ট" },
  status: { en: "Status", bn: "স্ট্যাটাস" },
  orderInfo: { en: "Order Information", bn: "অর্ডারের তথ্য" },
  customerInfo: { en: "Customer Information", bn: "কাস্টমারের তথ্য" },
  paymentInfo: { en: "Payment Information", bn: "পেমেন্টের তথ্য" },

  // Sort
  sortBy: { en: "Sort by", bn: "সর্ট করুন" },
  sortDeliveryDate: { en: "Delivery Date", bn: "ডেলিভারি তারিখ" },
  sortNewest: { en: "Newest first", bn: "নতুন আগে" },
  sortOldest: { en: "Oldest first", bn: "পুরানো আগে" },
  sortAmountHigh: { en: "Amount: High to Low", bn: "পরিমাণ: বেশি থেকে কম" },
  sortAmountLow: { en: "Amount: Low to High", bn: "পরিমাণ: কম থেকে বেশি" },
  sortStatus: { en: "Status", bn: "স্ট্যাটাস" },
  sortNameAZ: { en: "Name: A to Z", bn: "নাম: A থেকে Z" },
  sortNameZA: { en: "Name: Z to A", bn: "নাম: Z থেকে A" },
  sortMostOrders: { en: "Most orders", bn: "সবচেয়ে বেশি অর্ডার" },
  sortHighestDue: { en: "Highest due", bn: "সবচেয়ে বেশি বাকি" },

  // Export
  export: { en: "Export", bn: "এক্সপোর্ট" },
  exportCSV: { en: "Export as CSV", bn: "CSV এ এক্সপোর্ট" },
  exportPDF: { en: "Export as PDF", bn: "PDF এ এক্সপোর্ট" },
  exportSuccess: { en: "Exported successfully!", bn: "সফলভাবে এক্সপোর্ট হয়েছে!" },

  // Global Search
  globalSearchPlaceholder: { en: "Search customers...", bn: "কাস্টমার সার্চ করুন..." },
  noResults: { en: "No results found", bn: "কোনো ফলাফল পাওয়া যায়নি" },
  viewCustomer: { en: "View History", bn: "হিস্ট্রি দেখুন" },

  // Language
  language: { en: "EN", bn: "বাং" },

  // Workers
  workersTitle: { en: "Workers", bn: "কর্মীদের তালিকা" },
  workersSubtitle: { en: "Manage your workers list", bn: "কর্মীদের তালিকা পরিচালনা করুন" },
  workersAdd: { en: "Add", bn: "যোগ করুন" },
  workersAddNew: { en: "Add New Worker", bn: "নতুন কর্মী যোগ করুন" },
  workersList: { en: "Workers List", bn: "কর্মীদের তালিকা" },
  workersCount: { en: "workers", bn: "জন" },
  workersEmpty: { en: "No workers added yet", bn: "এখনও কোনো কর্মী যোগ করা হয়নি" },
  workersAdded: { en: "Worker added", bn: "কর্মী যোগ হয়েছে" },
  workersUpdated: { en: "Worker updated", bn: "কর্মী আপডেট হয়েছে" },
  workersDeleted: { en: "Worker removed", bn: "কর্মী মুছে ফেলা হয়েছে" },
  workersBothNamesRequired: { en: "Both Bengali and English names are required", bn: "বাংলা ও ইংরেজি দুটো নামই দিতে হবে" },
  workersDeleteTitle: { en: "Delete Worker", bn: "কর্মী মুছুন" },
  workersDeleteDesc: { en: "Are you sure you want to remove", bn: "আপনি কি নিশ্চিত মুছে ফেলতে চান" },

  // Measurement Fields - Shirt
  measurementLength: { en: "Length", bn: "লম্বা" },
  measurementChest: { en: "Chest", bn: "বডি" },
  measurementBelly: { en: "Belly", bn: "পেট" },
  measurementHip: { en: "Hip", bn: "হিপ" },
  measurementShoulder: { en: "Shoulder", bn: "সোল্ডার" },
  measurementSleeve: { en: "Sleeve", bn: "হাতা" },
  measurementCollar: { en: "Collar", bn: "কলার" },
  measurementFront: { en: "Front", bn: "সামনা" },
  measurementBack: { en: "Back", bn: "ব্যাক" },
  measurementHandWrittenNotes: { en: "Hand Written Notes", bn: "হাতে লেখা নোট" },
  measurementShirtMeasurements: { en: "Shirt Measurements", bn: "শার্ট মেজারমেন্ট" },

  // Measurement Fields - Pant
  measurementWaist: { en: "Waist", bn: "কোমর" },
  measurementHigh: { en: "High", bn: "হাই" },
  measurementThai: { en: "Thai", bn: "রান" },
  measurementLegOpen: { en: "Leg Open", bn: "মৌরি" },
  measurementPantMeasurements: { en: "Pant Measurements", bn: "প্যান্ট মেজারমেন্ট" },
};

export const currentLang = ref(localStorage.getItem('app-language') || 'bn');

watchEffect(() => {
  localStorage.setItem('app-language', currentLang.value);
  if (typeof document !== 'undefined') {
    document.documentElement.lang = currentLang.value;
    if (currentLang.value === 'bn') {
      document.documentElement.classList.add('font-bangla');
    } else {
      document.documentElement.classList.remove('font-bangla');
    }
  }
});

export function t(key) {
  return translations[key]?.[currentLang.value] || translations[key]?.en || key;
}

export function initLanguage() {
  const lang = localStorage.getItem('app-language') || 'bn';
  currentLang.value = lang;
}

export function useLanguage() {
  const setLang = (newLang) => {
    currentLang.value = newLang;
  };

  return {
    lang: currentLang,
    setLang,
    t,
  };
}
