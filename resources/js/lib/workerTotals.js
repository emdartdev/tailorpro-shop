// Shared totals logic for staff/worker pages.
export const STAFF_PAYMENT_MARKER = "__staff_payment__";

const num = (v) => Number(v || 0) || 0;

/** A row counts toward totals if it has any meaningful content. */
export function isFilledEntry(e) {
  if (!e) return false;
  if (e.item === STAFF_PAYMENT_MARKER) return false;
  const hasName = (e.name || "").toString().trim().length > 0;
  const hasItem = (e.item || "").toString().trim().length > 0;
  return hasName || hasItem || num(e.unit_price) > 0 || num(e.khoroch) > 0;
}

/** Canonical per-row total: quantity * unit_price. Never trust stored total_taka. */
export function rowTotal(e) {
  const calc = num(e.quantity) * num(e.unit_price);
  return calc > 0 ? calc : num(e.total_taka);
}

/** Compute totals from a list of entries (filters out empty + payment-marker rows). */
export function computeWorkerTotals(entries) {
  const filled = (entries || []).filter(isFilledEntry);
  const totalAmount = filled.reduce((s, e) => s + rowTotal(e), 0);
  const totalKhoroch = filled.reduce((s, e) => s + num(e.khoroch), 0);
  return { totalAmount, totalKhoroch, netPayable: totalAmount - totalKhoroch };
}

/** Shared localStorage key used by both StaffSummary and WorkerEntries pages. */
export const WORKER_MONTH_FILTER_KEY = "worker_month_filter";

/**
 * Returns true if dateStr (YYYY-MM-DD) is inside the given month filter value.
 * monthFilter format: "all" | "YYYY-M" (M is 0-indexed, matching Date.getMonth()).
 */
export function entryInMonthFilter(dateStr, monthFilter) {
  if (!monthFilter || monthFilter === "all") return true;
  if (!dateStr) return false;

  // Format 1: Standard YYYY-MM (e.g. "2026-10")
  if (/^\d{4}-\d{2}$/.test(monthFilter)) {
    return dateStr.startsWith(monthFilter);
  }

  // Format 2: YYYY-M (0-indexed or 1-indexed)
  const parts = monthFilter.split("-");
  if (parts.length === 2) {
    const y = Number(parts[0]);
    const m = Number(parts[1]);
    const dateParts = dateStr.split("-");
    const ey = Number(dateParts[0]);
    const em = Number(dateParts[1]);
    return ey === y && (em === m || (em - 1) === m);
  }

  return dateStr.startsWith(monthFilter);
}
