/**
 * Dynamic SEO and Metadata Manager for TailorPro
 * Supports dynamic document title, meta descriptions, canonical URLs, and robots directives.
 */

const DEFAULT_TITLE = 'One Raymond TailorPro | Tailor & Apparel Management ERP';
const DEFAULT_DESCRIPTION = 'One Raymond TailorPro is an advanced tailoring management system featuring military airman uniform tracking, custom officer measurements, worker piece-rate payroll, and delivery invoicing.';
const DEFAULT_ROBOTS_PUBLIC = 'index, follow, max-image-preview:large';
const DEFAULT_ROBOTS_PRIVATE = 'noindex, nofollow';

const ROUTE_SEO_MAP = {
  Login: {
    title: 'Login to One Raymond TailorPro | Tailor ERP System',
    description: 'Sign in to One Raymond TailorPro to access officer orders, airman uniform tracking, staff payroll entries, and customer history.',
    robots: DEFAULT_ROBOTS_PUBLIC,
  },
  ResetPassword: {
    title: 'Reset Password | One Raymond TailorPro',
    description: 'Reset your account password for One Raymond TailorPro ERP software.',
    robots: DEFAULT_ROBOTS_PUBLIC,
  },
  Orders: {
    title: 'Officer Orders Management | One Raymond TailorPro',
    description: 'Manage officer tailoring orders, cutting, stitching measurements, delivery tracking, and payments.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  CreateOrder: {
    title: 'New Officer Order & Measurements | One Raymond TailorPro',
    description: 'Record new tailoring order with shirt and pant measurements, item catalog, and advance payments.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  EditOrder: {
    title: 'Edit Order & Measurements | One Raymond TailorPro',
    description: 'Update tailoring order details, measurements, delivery dates, and notes.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  OrderDetails: {
    title: 'Order Details & Invoice | One Raymond TailorPro',
    description: 'View order breakdown, detailed measurements, staff assignment, and print thermal slips.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  AirmanOrders: {
    title: 'Airman Uniform Orders | One Raymond TailorPro',
    description: 'Manage military airman uniform orders, IV numbers, garment categories, and batch statuses.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  CreateAirmanOrder: {
    title: 'New Airman Order & Uniform Slip | One Raymond TailorPro',
    description: 'Create military airman tailoring entry with IV number, running year, and item allocations.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  Customers: {
    title: 'Customer Directory | One Raymond TailorPro',
    description: 'Search customer database, contact numbers, ranks, and historical orders.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  CustomerHistory: {
    title: 'Customer Order History & Measurements | One Raymond TailorPro',
    description: 'Inspect complete historical measurements, past orders, and repeat tailoring preferences.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  Workers: {
    title: 'Staff Directory & Monthly Work Folders | One Raymond TailorPro',
    description: 'Manage tailor staff, monthly entry folders, piece-rate assignments, and archive history.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  StaffSummary: {
    title: 'Monthly Staff Payroll Summary | One Raymond TailorPro',
    description: 'Comprehensive staff piece-rate monthly work summary, advances, commissions, and net payables.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  WorkerEntries: {
    title: 'Staff Monthly Work Sheet | One Raymond TailorPro',
    description: 'Daily piece-rate entry sheet with serial tracking, item rates, cash advance, and balance ledger.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  Delivery: {
    title: 'Delivery Management & Invoice Center | One Raymond TailorPro',
    description: 'Track orders ready for delivery, collect balances, generate thermal slips, and download A4 invoices.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  Dashboard: {
    title: 'Analytics & Operations Dashboard | One Raymond TailorPro',
    description: 'Overview of daily, weekly, and monthly orders, delivery pipelines, revenue, and active staff.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  Report: {
    title: 'Financial & Order Reports | One Raymond TailorPro',
    description: 'Detailed analytics and exportable financial reports for One Raymond TailorPro.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  Settings: {
    title: 'System Settings & Role Management | One Raymond TailorPro',
    description: 'Configure branch details, user roles, security settings, and PWA options.',
    robots: DEFAULT_ROBOTS_PRIVATE,
  },
  NotFound: {
    title: 'Page Not Found (404) | One Raymond TailorPro',
    description: 'The requested page could not be found on One Raymond TailorPro.',
    robots: 'noindex, nofollow',
  },
};

/**
 * Updates DOM head tags dynamically for client-side route transitions
 */
export function updateSeo(route) {
  if (typeof document === 'undefined') return;

  const seoData = ROUTE_SEO_MAP[route.name] || {
    title: route.meta?.title || DEFAULT_TITLE,
    description: route.meta?.description || DEFAULT_DESCRIPTION,
    robots: route.meta?.public ? DEFAULT_ROBOTS_PUBLIC : DEFAULT_ROBOTS_PRIVATE,
  };

  // 1. Update Title
  document.title = seoData.title;

  // 2. Update Meta Description
  let metaDesc = document.querySelector('meta[name="description"]');
  if (!metaDesc) {
    metaDesc = document.createElement('meta');
    metaDesc.name = 'description';
    document.head.appendChild(metaDesc);
  }
  metaDesc.setAttribute('content', seoData.description);

  // 3. Update Robots
  let metaRobots = document.querySelector('meta[name="robots"]');
  if (!metaRobots) {
    metaRobots = document.createElement('meta');
    metaRobots.name = 'robots';
    document.head.appendChild(metaRobots);
  }
  metaRobots.setAttribute('content', seoData.robots);

  // 4. Update Canonical Link
  let linkCanonical = document.querySelector('link[rel="canonical"]');
  if (!linkCanonical) {
    linkCanonical = document.createElement('link');
    linkCanonical.rel = 'canonical';
    document.head.appendChild(linkCanonical);
  }
  const cleanUrl = `${window.location.origin}${route.path}`;
  linkCanonical.setAttribute('href', cleanUrl);

  // 5. Update Open Graph Tags
  const updateOgTag = (property, content) => {
    let el = document.querySelector(`meta[property="${property}"]`);
    if (!el) {
      el = document.createElement('meta');
      el.setAttribute('property', property);
      document.head.appendChild(el);
    }
    el.setAttribute('content', content);
  };

  updateOgTag('og:title', seoData.title);
  updateOgTag('og:description', seoData.description);
  updateOgTag('og:url', cleanUrl);

  // 6. Update Twitter Card Tags
  const updateTwitterTag = (name, content) => {
    let el = document.querySelector(`meta[name="${name}"]`);
    if (!el) {
      el = document.createElement('meta');
      el.setAttribute('name', name);
      document.head.appendChild(el);
    }
    el.setAttribute('content', content);
  };

  updateTwitterTag('twitter:title', seoData.title);
  updateTwitterTag('twitter:description', seoData.description);
}
