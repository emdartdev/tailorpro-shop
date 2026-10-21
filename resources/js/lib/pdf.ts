import jsPDF from "jspdf";
import { hindSiliguriBoldBase64 } from "./hindSiliguriBold";
import { INVOICE_CONTACT_PNG_BASE64 } from "./invoiceContactImage";

// Aspect ratio of the invoice contact image (width / height)
const INVOICE_CONTACT_ASPECT = 708 / 172;

function drawInvoiceContact(doc: jsPDF, x: number, y: number, height: number) {
  const width = height * INVOICE_CONTACT_ASPECT;
  doc.addImage(INVOICE_CONTACT_PNG_BASE64, "PNG", x, y, width, height, undefined, "FAST");
}

const formatItemType = (value: string) => (value ? value.charAt(0).toUpperCase() + value.slice(1) : "-");

// Normalize measurements: DB column is JSON, but in some cases Supabase returns
// it as a string (e.g. when the row was inserted with a stringified payload).
// Always coerce to a plain object so downstream key lookups work.
function normalizeMeasurements(raw: any): Record<string, any> {
  if (!raw) return {};
  if (typeof raw === "string") {
    try {
      const parsed = JSON.parse(raw);
      return parsed && typeof parsed === "object" && !Array.isArray(parsed) ? parsed : {};
    } catch {
      return {};
    }
  }
  if (typeof raw === "object" && !Array.isArray(raw)) return raw as Record<string, any>;
  return {};
}

// Aggregate duplicate items back into quantity > 1 for display
function aggregateItemsForInvoice(items: any[]): any[] {
  const grouped = new Map<string, any>();
  items.forEach((item) => {
    const normalizedMeasurements = normalizeMeasurements(item.measurements);
    const normalizedName = String(item.item_name || "").trim();
    const normalizedNotes = item.notes || "";
    const normalizedIvNumber = String(item.iv_number || "").trim();
    const key = JSON.stringify({
      item_type: item.item_type,
      item_name: normalizedName,
      notes: normalizedNotes,
      iv_number: normalizedIvNumber,
      measurements: normalizedMeasurements,
    });
    const existing = grouped.get(key);
    if (existing) {
      existing.quantity = Number(existing.quantity || 1) + Number(item.quantity || 1);
    } else {
      grouped.set(key, {
        ...item,
        item_name: normalizedName,
        notes: normalizedNotes,
        iv_number: normalizedIvNumber,
        measurements: normalizedMeasurements,
        quantity: Math.max(1, Number(item.quantity || 1)),
      });
    }
  });
  return Array.from(grouped.values());
}

const hasBengali = (text: string) => /[\u0980-\u09FF]/.test(text || "");

function setDocFont(doc: jsPDF, text: string, style: "bold" | "normal" = "normal") {
  if (hasBengali(text)) {
    doc.setFont("HindSiliguri", "bold");
  } else {
    doc.setFont("helvetica", style);
  }
}

function openAndPrintPdf(doc: jsPDF) {
  const pdfBlob = doc.output("blob");
  const url = URL.createObjectURL(pdfBlob);
  const printWindow = window.open(url, "_blank");
  if (printWindow) {
    printWindow.focus();
    printWindow.addEventListener("load", () => {
      try {
        printWindow.print();
      } catch (e) {}
    });
    setTimeout(() => {
      try {
        printWindow.print();
      } catch (e) {}
    }, 700);
  } else {
    const iframe = document.createElement("iframe");
    iframe.style.position = "fixed";
    iframe.style.right = "0";
    iframe.style.bottom = "0";
    iframe.style.width = "0";
    iframe.style.height = "0";
    iframe.style.border = "none";
    iframe.src = url;
    document.body.appendChild(iframe);
    setTimeout(() => {
      try {
        iframe.contentWindow?.focus();
        iframe.contentWindow?.print();
      } catch (e) {
        window.location.href = url;
      }
    }, 500);
  }
}

export function generateInvoicePDF(order: any, customer: any, rawItems: any[], isDeliveryInvoice = false) {
  // Compute a friendly customer display name. Airman orders often have no
  // customer.name (phone missing or "-"), so fall back to "Airman <IV/BD>"
  // instead of showing "N/A" / "Unknown" on the invoice.
  const isAirman = !customer?.phone || customer?.phone === "-";
  const fallbackName = isAirman
    ? `Airman ${order?.iv_number || customer?.bd_number || order?.bd_number || ""}`.trim()
    : "";
  const resolvedName =
    (customer?.name && String(customer.name).trim()) ||
    (fallbackName && fallbackName !== "Airman" ? fallbackName : "") ||
    "N/A";
  customer = { ...(customer || {}), name: resolvedName };
  const items = aggregateItemsForInvoice(rawItems);
  const doc = new jsPDF({ orientation: "portrait", format: "a4", unit: "mm" });

  // Register Hind Siliguri Bold for Bengali text
  doc.addFileToVFS("HindSiliguri-Bold.ttf", hindSiliguriBoldBase64);
  doc.addFont("HindSiliguri-Bold.ttf", "HindSiliguri", "normal");
  doc.addFont("HindSiliguri-Bold.ttf", "HindSiliguri", "bold");

  const w = doc.internal.pageSize.getWidth();   // 210
  const pageH = doc.internal.pageSize.getHeight(); // 297
  const halfH = pageH / 2; // 148.5
  const margin = 10;
  const contentW = w - margin * 2;

  if (isDeliveryInvoice) {
    renderDeliveryInvoice(doc, order, customer, items, w, halfH, margin, contentW);
  } else {
    renderStandardInvoice(doc, order, customer, items, w, halfH, margin, contentW);
  }

  // ── CUT LINE at midpoint ──
  const cutLineY = halfH + 1;
  doc.setDrawColor(180, 180, 180);
  doc.setLineWidth(0.3);
  doc.setLineDashPattern([3, 2], 0);
  doc.line(5, cutLineY, w - 5, cutLineY);
  doc.setFontSize(6);
  doc.setTextColor(180, 180, 180);
  doc.text("✂  Cut here", w / 2, halfH + 4, { align: "center" });

  openAndPrintPdf(doc);
}

function renderDeliveryInvoice(doc: jsPDF, order: any, customer: any, items: any[], w: number, halfH: number, margin: number, contentW: number) {
  const headerBg: [number, number, number] = [88, 28, 52];
  const accentGold: [number, number, number] = [200, 160, 60];
  const darkRed: [number, number, number] = [140, 30, 50];
  const warmAmber: [number, number, number] = [220, 160, 50];
  const tableHeaderBg: [number, number, number] = [100, 20, 35];
  const creamBg: [number, number, number] = [248, 240, 232];
  const lightPinkRow: [number, number, number] = [255, 248, 244];
  const footerBg: [number, number, number] = [88, 28, 52];

  // ── HEADER BAR ──
  doc.setFillColor(...headerBg);
  doc.rect(0, 0, w, 20, "F");
  // Gold line at top
  doc.setFillColor(...accentGold);
  doc.rect(0, 0, w, 1, "F");
  // Gold line below header
  doc.setFillColor(...accentGold);
  doc.rect(0, 18.5, w, 1.5, "F");

  // Header text
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(12);
  doc.setFont("helvetica", "bold");
  doc.text("ONE RAYMOND TAILORS & FABRICS", margin + 2, 7.5);

  // DELIVERY INVOICE label
  doc.setTextColor(...accentGold);
  doc.setFontSize(12);
  doc.setFont("helvetica", "bold");
  doc.text("DELIVERY INVOICE", w - margin, 8, { align: "right" });
  const orderNum = order.order_number ? `#${order.order_number}` : `#${order.id.substring(0, 8)}`;
  doc.setFontSize(16);
  doc.setTextColor(255, 255, 255);
  doc.text(orderNum, w - margin, 16, { align: "right" });

  // Contact numbers with payment icons (white on dark header)
  drawInvoiceContact(doc, margin + 2, 10.5, 6.5);

  // ── CONTENT CARD ──
  const cardX = margin - 2;
  const cardY = 22;
  const cardH = halfH - cardY - 14;
  const cardW = contentW + 4;
  const innerLeft = margin + 2; // consistent left padding inside card
  doc.setFillColor(...creamBg);
  doc.roundedRect(cardX, cardY, cardW, cardH, 3, 3, "F");

  // ── BILL TO banner (red ribbon) ──
  const billToY = cardY + 5;
  doc.setFillColor(...darkRed);
  doc.rect(cardX, billToY, 44, 8, "F");
  doc.triangle(cardX + 44, billToY, cardX + 44, billToY + 8, cardX + 48, billToY + 4, "F");

  // User icon circle
  doc.setFillColor(255, 255, 255);
  doc.circle(cardX + 7, billToY + 4, 3, "F");
  doc.setFillColor(...darkRed);
  doc.circle(cardX + 7, billToY + 2.5, 0.9, "F");
  doc.ellipse(cardX + 7, billToY + 5.5, 2, 1.2, "F");
  doc.setFillColor(255, 255, 255);
  doc.circle(cardX + 7, billToY + 2.5, 0.9, "F");
  doc.setFillColor(...darkRed);
  doc.circle(cardX + 7, billToY + 2.3, 0.7, "F");

  doc.setTextColor(255, 255, 255);
  doc.setFontSize(9);
  doc.setFont("helvetica", "bold");
  doc.text("BILL TO", cardX + 12, billToY + 5.5);

  // ── Date boxes on right ──
  const dateBoxW = 52;
  const dateBoxX = cardX + cardW - dateBoxW - 4;
  const dateBoxY = billToY;

  // Order date box
  doc.setFillColor(255, 252, 248);
  doc.setDrawColor(220, 210, 200);
  doc.setLineWidth(0.3);
  doc.roundedRect(dateBoxX, dateBoxY, dateBoxW, 12, 1.5, 1.5, "FD");

  doc.setFontSize(6);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(...darkRed);
  doc.text("ORDER DATE", dateBoxX + 4, dateBoxY + 4);
  doc.setFontSize(10);
  doc.setFont("helvetica", "normal");
  doc.setTextColor(30, 30, 30);
  doc.text(order.order_date || "-", dateBoxX + 4, dateBoxY + 10);

  // Delivered date box
  const delDateY = dateBoxY + 14;
  doc.setFillColor(255, 252, 248);
  doc.setDrawColor(220, 210, 200);
  doc.roundedRect(dateBoxX, delDateY, dateBoxW, 12, 1.5, 1.5, "FD");

  doc.setFontSize(6);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(...darkRed);
  doc.text("DELIVERED DATE", dateBoxX + 4, delDateY + 4);
  doc.setFontSize(10);
  doc.setFont("helvetica", "normal");
  doc.setTextColor(30, 30, 30);
  doc.text(order.delivered_date || order.delivery_date || "-", dateBoxX + 4, delDateY + 10);

  // ── Customer name & BD ──
  const nameY = billToY + 15;
  doc.setFontSize(15);
  const custName = customer?.name || "N/A";
  setDocFont(doc, custName, "bold");
  doc.setTextColor(30, 30, 30);
  const maxNameW = dateBoxX - innerLeft - 4;
  const nameLines = doc.splitTextToSize(custName, maxNameW);
  doc.text(nameLines[0], innerLeft, nameY);

  doc.setFontSize(8);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(...darkRed);
  doc.text("BD NUMBER", innerLeft, nameY + 5);
  doc.setFontSize(13);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(30, 30, 30);
  doc.text(order.bd_number || "-", innerLeft, nameY + 11);

  // ── ITEMS TABLE ──
  let y = nameY + 15;
  const rowH = 7.5;
  const tableLeft = innerLeft;
  const tableRight = cardX + cardW - 4;
  const tableW = tableRight - tableLeft;
  const colName = tableLeft;
  const colQty = tableLeft + tableW * 0.55;
  const colPrice = tableLeft + tableW * 0.75;

  // Table header
  doc.setFillColor(...tableHeaderBg);
  doc.roundedRect(tableLeft, y - 2, tableW, 6.5, 1.5, 1.5, "F");
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(6.5);
  doc.setFont("helvetica", "bold");
  doc.text("ITEM NAME", colName + 3, y + 2);
  doc.text("QTY", colQty, y + 2);
  doc.text("PRICE", colPrice, y + 2);
  y += 6.5;

  // Table rows
  doc.setFontSize(10);
  let itemsTotal = 0;
  items.forEach((item, idx) => {
    if (idx % 2 === 0) {
      doc.setFillColor(...lightPinkRow);
      doc.rect(tableLeft, y - 2, tableW, rowH, "F");
    }
    // Row border
    doc.setDrawColor(230, 220, 215);
    doc.setLineWidth(0.2);
    doc.line(tableLeft, y + rowH - 2, tableRight, y + rowH - 2);

    const displayName = item.item_name || formatItemType(item.item_type);
    setDocFont(doc, displayName, "bold");
    doc.setTextColor(50, 40, 40);
    doc.text(displayName, colName + 3, y + 2.8);

    doc.setFont("helvetica", "normal");
    doc.setTextColor(50, 50, 50);
    const qty = item.quantity || 1;
    doc.text(String(qty), colQty, y + 2.8);

    const unitPrice = Number(item.price) || 0;
    const lineTotal = unitPrice * qty;
    itemsTotal += lineTotal;
    doc.text(`BDT ${lineTotal.toLocaleString()}`, tableRight - 2, y + 2.8, { align: "right" });
    y += rowH;
  });

  // ── DELIVERY INFO (left) + PAYMENT SUMMARY (right) ──
  y += 3;
  const summaryLabelX = colQty - 5;
  const summaryValX = tableRight - 2;

  const subtotal = Number(order.total_amount) || itemsTotal;
  const advanceAmount = Number(order.advance) || 0;
  const dueAmount = Number(order.due) ?? (subtotal - advanceAmount);
  const cashReceived = Math.max(0, subtotal - advanceAmount - dueAmount);

  // Delivery info on left (aligned with table start)
  const delivInfoY = y;
  if (order.delivered_date || order.receiver_name) {
    doc.setFontSize(7);
    doc.setFont("helvetica", "bold");
    doc.setTextColor(...darkRed);
    doc.text("DELIVERY INFO", innerLeft, delivInfoY);
    let diY = delivInfoY + 5;
    doc.setFontSize(8);
    doc.setFont("helvetica", "normal");
    doc.setTextColor(50, 50, 50);
    if (order.delivered_date) {
      doc.text(`Delivered Date: ${order.delivered_date}`, innerLeft, diY);
      diY += 4.5;
    }
    if (order.receiver_name) {
      setDocFont(doc, order.receiver_name, "normal");
      doc.text(`Received By: ${order.receiver_name}`, innerLeft, diY);
    }
  }

  // Payment summary on right
  doc.setFontSize(9);
  doc.setFont("helvetica", "normal");
  doc.setTextColor(80, 80, 80);
  doc.text("Subtotal:", summaryLabelX, y);
  doc.setTextColor(...darkRed);
  doc.setFont("helvetica", "bold");
  doc.text(`BDT ${subtotal.toLocaleString()}`, summaryValX, y, { align: "right" });

  y += 5;
  doc.setFont("helvetica", "normal");
  doc.setTextColor(80, 80, 80);
  doc.text("Advance Paid:", summaryLabelX, y);
  doc.setTextColor(0, 128, 100);
  doc.setFont("helvetica", "bold");
  doc.text(`- BDT ${advanceAmount.toLocaleString()}`, summaryValX, y, { align: "right" });

  if (cashReceived > 0) {
    y += 5;
    doc.setFont("helvetica", "normal");
    doc.setTextColor(80, 80, 80);
    doc.text("Cash Received:", summaryLabelX, y);
    doc.setTextColor(0, 128, 100);
    doc.setFont("helvetica", "bold");
    doc.text(`- BDT ${cashReceived.toLocaleString()}`, summaryValX, y, { align: "right" });
  }

  // ── DUE BADGE ──
  y += 7;
  const dueBarX = summaryLabelX - 5;
  const dueBarW = summaryValX - dueBarX + 4;
  doc.setFillColor(...warmAmber);
  doc.roundedRect(dueBarX, y - 4.5, dueBarW, 11, 3, 3, "F");
  doc.setFillColor(240, 200, 90);
  doc.roundedRect(dueBarX + 1, y - 3.5, dueBarW - 2, 4, 2, 2, "F");
  doc.setFillColor(...warmAmber);
  doc.rect(dueBarX + 1, y - 0.5, dueBarW - 2, 5, "F");

  doc.setTextColor(255, 255, 255);
  doc.setFontSize(10);
  doc.setFont("helvetica", "bold");
  doc.text("DUE", dueBarX + 8, y + 3);
  doc.setFontSize(13);
  doc.text(`BDT ${dueAmount.toLocaleString()}`, dueBarX + dueBarW - 4, y + 3, { align: "right" });

  // ── PAID WATERMARK ──
  if (dueAmount <= 0 && order.status === "delivered") {
    doc.saveGraphicsState();
    doc.setGState(new (doc as any).GState({ opacity: 0.15 }));
    doc.setTextColor(46, 160, 100);
    doc.setFontSize(50);
    doc.setFont("helvetica", "bold");
    doc.text("PAID", w / 2, halfH / 2 + 15, { align: "center", angle: -25 });
    doc.restoreGraphicsState();

    doc.saveGraphicsState();
    doc.setGState(new (doc as any).GState({ opacity: 0.10 }));
    doc.setTextColor(212, 100, 100);
    doc.setFontSize(28);
    doc.setFont("helvetica", "bold");
    doc.text("DELIVERED", w / 2, halfH / 2 + 30, { align: "center", angle: -25 });
    doc.restoreGraphicsState();
  }

  // ── FOOTER ──
  const footerH = 14;
  doc.setFillColor(...footerBg);
  doc.rect(0, halfH - footerH, w, footerH, "F");
  doc.setFillColor(...accentGold);
  doc.rect(0, halfH - footerH, w, 1, "F");
  doc.setFillColor(...accentGold);
  doc.rect(0, halfH - 1, w, 1, "F");

  doc.setFontSize(6.5);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(...accentGold);
  const footerY = halfH - footerH + 5;
  doc.text("Quality Tailoring  |  Timely Delivery  |  Customer Satisfaction", w / 2, footerY, { align: "center" });

  doc.setFontSize(5.5);
  doc.setFont("helvetica", "normal");
  doc.setTextColor(200, 200, 200);
  doc.text("Address : Base tailor shop. BAF base bashar Dhaka cant 1206", w / 2, footerY + 4.5, { align: "center" });
}

function renderStandardInvoice(doc: jsPDF, order: any, customer: any, items: any[], w: number, halfH: number, margin: number, contentW: number) {
  const headerBg: [number, number, number] = [15, 42, 100];
  const accentGold: [number, number, number] = [200, 165, 60];
  const darkNavy: [number, number, number] = [10, 30, 70];
  const goldLabel: [number, number, number] = [180, 140, 40];
  const contentBg: [number, number, number] = [240, 242, 248];
  const lightRow: [number, number, number] = [248, 249, 252];
  const footerH = 14;

  // ── HEADER BAR ──
  doc.setFillColor(...headerBg);
  doc.rect(0, 0, w, 20, "F");
  doc.setFillColor(...accentGold);
  doc.rect(0, 0, w, 1, "F");
  doc.setFillColor(...accentGold);
  doc.rect(0, 18.5, w, 1.5, "F");

  doc.setTextColor(255, 255, 255);
  doc.setFontSize(12);
  doc.setFont("helvetica", "bold");
  doc.text("ONE RAYMOND TAILORS & FABRICS", margin + 2, 7.5);

  doc.setTextColor(...accentGold);
  doc.setFontSize(12);
  doc.setFont("helvetica", "bold");
  doc.text("INVOICE", w - margin, 8, { align: "right" });
  const orderNum = order.order_number ? `#${order.order_number}` : `#${order.id.substring(0, 8)}`;
  doc.setFontSize(16);
  doc.setTextColor(255, 255, 255);
  doc.text(orderNum, w - margin, 16, { align: "right" });

  // Contact numbers with payment icons (white on dark header)
  drawInvoiceContact(doc, margin + 2, 10.5, 6.5);

  // ── CONTENT CARD ──
  const cardX = margin - 2;
  const cardY = 22;
  const cardH = halfH - cardY - footerH - 2;
  const cardW = contentW + 4;
  const innerLeft = margin + 2;
  doc.setFillColor(...contentBg);
  doc.setDrawColor(...accentGold);
  doc.setLineWidth(0.5);
  doc.roundedRect(cardX, cardY, cardW, cardH, 3, 3, "FD");

  // ── BILL TO banner (gold ribbon) ──
  const billToY = cardY + 4;
  doc.setFillColor(...accentGold);
  doc.rect(cardX, billToY, 44, 7, "F");
  doc.triangle(cardX + 44, billToY, cardX + 44, billToY + 7, cardX + 48, billToY + 3.5, "F");

  // User icon circle
  doc.setFillColor(255, 255, 255);
  doc.circle(cardX + 7, billToY + 3.5, 2.5, "F");
  doc.setFillColor(...darkNavy);
  doc.circle(cardX + 7, billToY + 2, 0.6, "F");
  doc.ellipse(cardX + 7, billToY + 5, 1.8, 1, "F");
  doc.setFillColor(255, 255, 255);
  doc.circle(cardX + 7, billToY + 2, 0.6, "F");
  doc.setFillColor(...darkNavy);
  doc.circle(cardX + 7, billToY + 1.8, 0.5, "F");

  doc.setTextColor(255, 255, 255);
  doc.setFontSize(8);
  doc.setFont("helvetica", "bold");
  doc.text("BILL TO", cardX + 12, billToY + 4.8);

  // ── Date boxes on right ──
  const dateBoxW = 52;
  const dateBoxX = cardX + cardW - dateBoxW - 4;
  const dateBoxY = billToY;

  doc.setFillColor(255, 255, 255);
  doc.setDrawColor(220, 215, 210);
  doc.setLineWidth(0.3);
  doc.roundedRect(dateBoxX, dateBoxY, dateBoxW, 11, 1.5, 1.5, "FD");

  doc.setFillColor(...headerBg);
  doc.circle(dateBoxX + 5.5, dateBoxY + 5.5, 3, "F");
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(5);
  doc.text("Cal", dateBoxX + 4, dateBoxY + 6.5);

  doc.setFontSize(6);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(...goldLabel);
  doc.text("ORDER DATE", dateBoxX + 11, dateBoxY + 4);
  doc.setFontSize(9);
  doc.setFont("helvetica", "normal");
  doc.setTextColor(30, 30, 30);
  doc.text(order.order_date || "-", dateBoxX + 11, dateBoxY + 9);

  // Delivery date box
  const delDateY = dateBoxY + 13;
  doc.setFillColor(255, 255, 255);
  doc.setDrawColor(220, 215, 210);
  doc.roundedRect(dateBoxX, delDateY, dateBoxW, 11, 1.5, 1.5, "FD");

  doc.setFillColor(...headerBg);
  doc.circle(dateBoxX + 5.5, delDateY + 5.5, 3, "F");
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(5);
  doc.text("Del", dateBoxX + 4, delDateY + 6.5);

  doc.setFontSize(6);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(...goldLabel);
  doc.text("DELIVERY DATE", dateBoxX + 11, delDateY + 4);
  doc.setFontSize(9);
  doc.setFont("helvetica", "normal");
  doc.setTextColor(30, 30, 30);
  doc.text(order.delivery_date || "TBD", dateBoxX + 11, delDateY + 9);

  // ── Customer name & BD ──
  const nameY = billToY + 12;
  doc.setFontSize(14);
  const custName = customer?.name || "N/A";
  setDocFont(doc, custName, "bold");
  doc.setTextColor(30, 30, 30);
  const maxNameW = dateBoxX - innerLeft - 4;
  const nameLines = doc.splitTextToSize(custName, maxNameW);
  doc.text(nameLines[0], innerLeft, nameY);

  doc.setFontSize(7);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(...goldLabel);
  doc.text("BD NUMBER", innerLeft, nameY + 4.5);
  doc.setFontSize(12);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(30, 30, 30);
  doc.text(order.bd_number || "-", innerLeft, nameY + 10);

  // ── ITEMS TABLE ──
  let y = nameY + 14;
  const rowH = 7.5;
  const tableLeft = innerLeft;
  const tableRight = cardX + cardW - 4;
  const tableW = tableRight - tableLeft;
  const colName = tableLeft;
  const colQty = tableLeft + tableW * 0.60;
  const colPrice = tableLeft + tableW * 0.78;

  // Table header
  doc.setFillColor(...darkNavy);
  doc.roundedRect(tableLeft, y - 2, tableW, 6.5, 1.5, 1.5, "F");
  doc.setFillColor(...accentGold);
  doc.rect(tableLeft, y + 4, tableW, 0.5, "F");
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(6.5);
  doc.setFont("helvetica", "bold");
  doc.text("ITEM NAME", colName + 3, y + 2);
  doc.text("QTY", colQty, y + 2);
  doc.text("PRICE", colPrice, y + 2);
  y += 6.5;

  // Table rows
  doc.setFontSize(10);
  let itemsTotal = 0;
  items.forEach((item, idx) => {
    if (idx % 2 === 0) {
      doc.setFillColor(...lightRow);
      doc.rect(tableLeft, y - 2, tableW, rowH, "F");
    }
    doc.setDrawColor(230, 230, 235);
    doc.setLineWidth(0.2);
    doc.line(tableLeft, y + rowH - 2, tableRight, y + rowH - 2);

    const displayName = item.item_name || formatItemType(item.item_type);
    setDocFont(doc, displayName, "bold");
    doc.setTextColor(50, 40, 40);
    doc.text(displayName, colName + 3, y + 2.8);

    doc.setFont("helvetica", "normal");
    doc.setTextColor(50, 50, 50);
    const qty = item.quantity || 1;
    doc.text(String(qty), colQty + 2, y + 2.8);

    const unitPrice = Number(item.price) || 0;
    const lineTotal = unitPrice * qty;
    itemsTotal += lineTotal;
    doc.text(`BDT ${lineTotal.toLocaleString()}`, tableRight - 2, y + 2.8, { align: "right" });
    y += rowH;
  });

  // ── PAYMENT SUMMARY ──
  y += 2;

  // Subtle separator line
  doc.setDrawColor(200, 195, 190);
  doc.setLineWidth(0.3);
  doc.line(colQty - 8, y - 1, tableRight, y - 1);

  y += 3;
  const summaryLabelX = colQty - 5;
  const summaryValX = tableRight - 2;

  const subtotal = Number(order.total_amount) || itemsTotal;
  const advanceAmount = Number(order.advance) || 0;
  const dueAmount = Number(order.due) ?? (subtotal - advanceAmount);

  doc.setFontSize(8.5);
  doc.setFont("helvetica", "normal");
  doc.setTextColor(80, 80, 80);
  doc.text("Subtotal:", summaryLabelX, y);
  doc.setTextColor(...darkNavy);
  doc.setFont("helvetica", "bold");
  doc.text(`BDT ${subtotal.toLocaleString()}`, summaryValX, y, { align: "right" });

  y += 5;
  doc.setFont("helvetica", "normal");
  doc.setTextColor(80, 80, 80);
  doc.text("Advance Paid:", summaryLabelX, y);
  doc.setTextColor(0, 128, 100);
  doc.setFont("helvetica", "bold");
  doc.text(`- BDT ${advanceAmount.toLocaleString()}`, summaryValX, y, { align: "right" });

  // ── DUE BADGE ──
  y += 6;
  const dueBarX = summaryLabelX - 5;
  const dueBarW = summaryValX - dueBarX + 6;
  doc.setFillColor(...accentGold);
  doc.roundedRect(dueBarX, y - 4, dueBarW, 10, 2.5, 2.5, "F");
  doc.setFillColor(220, 185, 70);
  doc.roundedRect(dueBarX + 1, y - 3, dueBarW - 2, 3.5, 1.5, 1.5, "F");
  doc.setFillColor(...accentGold);
  doc.rect(dueBarX + 1, y - 0.5, dueBarW - 2, 4.5, "F");

  doc.setTextColor(255, 255, 255);
  doc.setFontSize(9);
  doc.setFont("helvetica", "bold");
  doc.text("DUE", dueBarX + 7, y + 2.5);
  doc.setFontSize(12);
  doc.text(`BDT ${dueAmount.toLocaleString()}`, dueBarX + dueBarW - 4, y + 2.5, { align: "right" });

  // ── PAID WATERMARK ──
  if (dueAmount <= 0 && order.status === "delivered") {
    doc.saveGraphicsState();
    doc.setGState(new (doc as any).GState({ opacity: 0.15 }));
    doc.setTextColor(0, 160, 0);
    doc.setFontSize(50);
    doc.setFont("helvetica", "bold");
    doc.text("PAID", w / 2, halfH / 2 + 15, { align: "center", angle: -25 });
    doc.restoreGraphicsState();
  }

  // ── FOOTER ──
  doc.setFillColor(...headerBg);
  doc.rect(0, halfH - footerH, w, footerH, "F");
  doc.setFillColor(...accentGold);
  doc.rect(0, halfH - footerH, w, 1, "F");
  doc.setFillColor(...accentGold);
  doc.rect(0, halfH - 1, w, 1, "F");

  doc.setFontSize(6.5);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(...accentGold);
  const footerY = halfH - footerH + 5;
  doc.text("Quality Tailoring  |  Timely Delivery  |  Customer Satisfaction", w / 2, footerY, { align: "center" });

  doc.setFontSize(5.5);
  doc.setFont("helvetica", "normal");
  doc.setTextColor(200, 200, 200);
  doc.text("Address : Base tailor shop. BAF base bashar Dhaka cant 1206", w / 2, footerY + 4.5, { align: "center" });
}

export function generateSlipPDF(
  order: any,
  customerOrItems?: any,
  itemsOrCustomerName?: any,
  filterItemIds?: string[],
) {
  let customer: any = null;
  let rawItems: any[] = [];
  let customerName = "";

  if (Array.isArray(customerOrItems)) {
    rawItems = customerOrItems;
    if (typeof itemsOrCustomerName === "string") {
      customerName = itemsOrCustomerName;
    } else if (itemsOrCustomerName && typeof itemsOrCustomerName === "object" && !Array.isArray(itemsOrCustomerName)) {
      customer = itemsOrCustomerName;
      customerName = customer.name || "";
    }
  } else if (customerOrItems && typeof customerOrItems === "object") {
    customer = customerOrItems;
    customerName = typeof customer === "string" ? customer : (customer?.name || "");
    if (Array.isArray(itemsOrCustomerName)) {
      rawItems = itemsOrCustomerName;
    } else if (itemsOrCustomerName && typeof itemsOrCustomerName === "object" && Array.isArray(itemsOrCustomerName.items)) {
      rawItems = itemsOrCustomerName.items;
    }
  } else if (typeof customerOrItems === "string") {
    customerName = customerOrItems;
    if (Array.isArray(itemsOrCustomerName)) {
      rawItems = itemsOrCustomerName;
    }
  }

  // Fallback to order properties if still empty
  if (!customerName) {
    customerName = order?.customer?.name || order?.customer_name || "";
  }
  if (!rawItems || rawItems.length === 0) {
    rawItems = Array.isArray(order?.items) ? order.items : [];
  }
  const items = rawItems;

  const doc = new jsPDF({ orientation: "portrait", format: "a4", unit: "mm" });

  // Register Hind Siliguri Bold for Bengali text
  doc.addFileToVFS("HindSiliguri-Bold.ttf", hindSiliguriBoldBase64);
  doc.addFont("HindSiliguri-Bold.ttf", "HindSiliguri", "normal");
  doc.addFont("HindSiliguri-Bold.ttf", "HindSiliguri", "bold");

  const pageW = doc.internal.pageSize.getWidth(); // 210
  const pageH = doc.internal.pageSize.getHeight(); // 297
  const margin = 8;
  const gap = 6;
  const slipW = (pageW - margin * 2 - gap) / 2;
  const bottomMargin = 10;

  const SHIRT_KEYS = ["length", "chest", "belly", "hip", "shoulder", "sleeve", "collar", "front", "back"];
  const PANT_KEYS = ["pant_length", "pant_waist", "pant_hip", "pant_high", "pant_thai", "pant_leg_open"];

  const DISPLAY_LABELS: Record<string, string> = {
    length: "Length", chest: "Chest", belly: "Belly", hip: "Hip", shoulder: "Shoulder",
    sleeve: "Sleeve", collar: "Collar", front: "Front", back: "Back",
    pant_length: "Length", pant_waist: "Waist", pant_hip: "Hip", pant_high: "High",
    pant_thai: "Thigh", pant_leg_open: "Leg Open",
  };

  const orderNum = order.order_number ? `#${order.order_number}` : `#${order.id?.substring(0, 8) || "0"}`;
  const deliveryDate = order.delivery_date || "";
  const isAirman = order.order_category === "airman";
  // Officers store one DB row per quantity unit; aggregate so each visible row produces one slip pair.
  const baseSlipItems = isAirman ? items : aggregateItemsForInvoice(items);
  // Apply optional filter (e.g. dedicated category download or single-item slip from details page).
  // Filter is applied AFTER building shared measurements so other items still contribute fallbacks.
  const slipItems = filterItemIds && filterItemIds.length
    ? baseSlipItems.filter((i: any) => filterItemIds.includes(i.id))
    : baseSlipItems;
  const legacyMeasurementNotes = slipItems.length === 1 && !slipItems.some((i) => i?.notes) ? order.measurement_notes || "" : "";
  const branchOrTradeVal = isAirman ? (order.trade || "") : (order.branch || "");

  // Measurements are shared across items in both officer and airman orders. Merge a fallback
  // map from ALL items in the order so an item row with empty/missing measurements still
  // renders correctly (e.g. when only one item carries the saved measurement values).
  const sharedMeasurements: Record<string, string> = (() => {
    const merged: Record<string, string> = {};
    items.forEach((it) => {
      const m = normalizeMeasurements(it?.measurements);
      Object.entries(m).forEach(([k, v]) => {
        const sv = typeof v === "string" ? v : v == null ? "" : String(v);
        if (sv && !merged[k]) merged[k] = sv;
      });
    });
    return merged;
  })();
  const ribonVal = order.ribon != null ? `R - ${order.ribon}` : "";

  // Build individual slip entries: each visible order item row produces its own shirt/pant slips.
  // Slips are expanded by quantity so an item with Quantity 2 prints 2 pairs of slips.
  type SlipEntry = {
    itemLabel: string;
    type: string;
    measurements: [string, string][];
    note: string;
    ivNumber?: string;
  };

  const slipEntries: SlipEntry[] = [];

  // Get shirt extra notes from order level
  const shirtExtraNotes = order.extra_notes || "";

  slipItems.forEach((item, idx) => {
    const rawMeas = normalizeMeasurements(item.measurements) as Record<string, string>;
    // Fall back to the order-wide shared measurements when this row is blank.
    const meas: Record<string, string> = {
      ...sharedMeasurements,
      ...Object.fromEntries(Object.entries(rawMeas).filter(([, v]) => v)),
    };

    const shirtMeas: [string, string][] = SHIRT_KEYS
      .map(k => [k, meas[k] || ""] as [string, string])
      .filter(([, v]) => String(v || "").trim() !== "");
    const pantMeas: [string, string][] = PANT_KEYS
      .map(k => [k, meas[k] || ""] as [string, string])
      .filter(([, v]) => String(v || "").trim() !== "");

    const itemLabel = item.item_name || (item.item_type ? item.item_type.charAt(0).toUpperCase() + item.item_type.slice(1) : "Item");
    const shirtHandNotes = item.notes || (idx === 0 ? legacyMeasurementNotes : "");
    const pantHandNotes = meas["__pant_notes"] || "";
    const pantExtraNotes = meas["__pant_extra_notes"] || "";

    // Combine hand written notes + extra notes for each type (no label prefix)
    const shirtCombinedNote = [shirtHandNotes, shirtExtraNotes].filter(Boolean).join("\n");
    const pantCombinedNote = [pantHandNotes, pantExtraNotes].filter(Boolean).join("\n");

    const qty = Math.max(1, Number(item.quantity || 1));
    if (isAirman) {
      // Airman: produce separate Shirt and Pant slips for each item.
      // "Full Sleeve" items get only a shirt slip (no pant measurements).
      const isFullSleeve = (itemLabel || "").toLowerCase().includes("full sleeve");
      const ivNumber = item.iv_number || order.iv_number || "";
      for (let q = 0; q < qty; q++) {
        if (shirtMeas.length > 0 || shirtHandNotes || shirtExtraNotes) {
          slipEntries.push({
            itemLabel,
            type: "Shirt",
            measurements: shirtMeas,
            note: shirtCombinedNote,
            ivNumber,
          });
        }
        if (!isFullSleeve && (pantMeas.length > 0 || pantHandNotes || pantExtraNotes)) {
          slipEntries.push({
            itemLabel,
            type: "Pant",
            measurements: pantMeas,
            note: pantCombinedNote,
            ivNumber,
          });
        }
      }
      return;
    }

    const OFFICER_SHIRT_ONLY_ITEMS = new Set([
      "half sleeve shirt", "full sleeve shirt", "punjabi", "jubba", "blazer",
      "meskit shirt", "koti", "dust coat", "mission genji", "fotua",
      "civil shirt", "half bush upor part",
    ]);
    const OFFICER_PANT_ONLY_ITEMS = new Set([
      "uniform pant", "civil pant", "paijama",
    ]);
    const normalizeItemKey = (s: string) =>
      String(s || "").toLowerCase().replace(/[_-]+/g, " ").replace(/\s+/g, " ").trim();
    const itemKeys = [normalizeItemKey(itemLabel), normalizeItemKey(item.item_name), normalizeItemKey(item.item_type)];
    const isOfficerShirtOnly = itemKeys.some(k => k && OFFICER_SHIRT_ONLY_ITEMS.has(k));
    const isOfficerPantOnly = itemKeys.some(k => k && OFFICER_PANT_ONLY_ITEMS.has(k));

    // Officer: produce one slip per allowed measurement type, expanded by quantity.
    for (let q = 0; q < qty; q++) {
      const hasShirtData = shirtMeas.length > 0 || Boolean(shirtCombinedNote);
      const hasPantData = pantMeas.length > 0 || Boolean(pantCombinedNote);

      if (!hasShirtData && !hasPantData) {
        // Fallback when no numerical measurements or notes are filled:
        // Produce slip(s) matching the item type so physical slips can be printed and written on.
        if (!isOfficerPantOnly) {
          slipEntries.push({ itemLabel, type: "Shirt", measurements: [], note: shirtCombinedNote });
        }
        if (!isOfficerShirtOnly) {
          slipEntries.push({ itemLabel, type: "Pant", measurements: [], note: pantCombinedNote });
        }
      } else {
        if (hasShirtData && !isOfficerPantOnly) {
          slipEntries.push({ itemLabel, type: "Shirt", measurements: shirtMeas, note: shirtCombinedNote });
        }
        if (hasPantData && !isOfficerShirtOnly) {
          slipEntries.push({ itemLabel, type: "Pant", measurements: pantMeas, note: pantCombinedNote });
        }
      }
    }
  });

  if (slipEntries.length === 0) {
    slipEntries.push({
      itemLabel: "Officer Order",
      type: "Slip",
      measurements: [],
      note: legacyMeasurementNotes || order.extra_notes || "",
    });
  }

  // Fixed slip height so all slips are the same size.
  // For officer orders with multiple item rows we shrink the height + row gap so
  // 5 pairs (10 slips / 5 rows) fit on a single A4 page.
  // Use total slip pairs (after quantity expansion) to decide compact layout so multi-qty
  // officer orders still fit on a single A4 page when possible.
  const officerSlipPairs = !isAirman ? Math.ceil(slipEntries.length / 2) : 0;
  const isMultiItemOfficer = !isAirman && officerSlipPairs >= 2;
  // 5 pairs (10 slips, 5 rows) must fit on one A4 page.
  // Usable height ≈ pageH(297) - margin(8) - bottomMargin(10) = 279mm.
  // 5*54 + 4*1 = 274mm ✓
  const FIXED_SLIP_H = isMultiItemOfficer ? 54 : 65;
  const ROW_GAP = isMultiItemOfficer ? 1 : 4;
  const FONT_SIZE = 12;
  const LINE_H = 4.8;

  // Render a single slip
  const renderSlip = (x: number, y: number, entry: SlipEntry, slipH: number) => {
    const left = x + 4;
    const right = x + slipW - 4;
    let cy = y + 5;
    const setSlipFont = () => doc.setFont("HindSiliguri", "bold");

    const ivNum = entry.ivNumber || (items.find(i => i.iv_number) || {}).iv_number || order.iv_number || "";

    if (isAirman) {
      // ── AIRMAN LAYOUT (matches reference design) ──
      // L1: #ORDERNO (large, navy) ~ Name ~ BD-Number          R - X
      // L2: Item:<name>[ (Pant)] | IV: <iv>
      // ── horizontal line ──
      // L3: numeric measurement values only, joined by " ~ "
      const ORDER_FONT = FONT_SIZE + 4;
      const NAVY_R = 11, NAVY_G = 37, NAVY_B = 87;

      // ----- Line 1 -----
      setSlipFont();
      doc.setFontSize(ORDER_FONT);
      doc.setTextColor(NAVY_R, NAVY_G, NAVY_B);
      const displayNum = ivNum ? `((#${ivNum}))` : `Order ${orderNum}`;
      doc.text(displayNum, left, cy);
      const orderW = doc.getTextWidth(displayNum);

      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const ribbonW = ribonVal ? doc.getTextWidth(ribonVal) : 0;
      if (ribonVal) {
        doc.text(ribonVal, right, cy, { align: "right" });
      }

      const nameBd = ` ~ ${customerName || "-"} ~ ${order.bd_number || "-"}`;
      const nameMaxW = right - left - orderW - (ribbonW ? ribbonW + 4 : 0);
      const nameTrunc = doc.splitTextToSize(nameBd, nameMaxW)[0] || nameBd;
      doc.text(nameTrunc, left + orderW, cy);

      // ----- Line 2 -----
      cy += LINE_H + 2;
      setSlipFont();
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const itemSuffix = entry.type === "Pant" ? " (Pant)" : "";
      const itemLine = `Item:${entry.itemLabel}${itemSuffix}${ivNum ? ` | IV: ${ivNum}` : ""}`;
      const itemTrunc = doc.splitTextToSize(itemLine, slipW - 8)[0] || itemLine;
      doc.text(itemTrunc, left, cy);

      // ----- Separator -----
      cy += LINE_H;
      doc.setLineWidth(0.4);
      doc.setDrawColor(0, 0, 0);
      doc.setLineDashPattern([], 0);
      doc.line(left, cy, right, cy);

      // ----- Measurements (values only, in form sort order) -----
      cy += LINE_H;
      setSlipFont();
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const measText = entry.measurements.map(([, value]) => value).join("  ~  ");
      const measLines = doc.splitTextToSize(measText, slipW - 8);
      doc.text(measLines, left, cy);
      cy += measLines.length * LINE_H;

      if (entry.note) {
        cy += 1;
        doc.setFontSize(FONT_SIZE);
        setSlipFont();
        doc.setTextColor(0, 0, 0);
        const noteLines = doc.splitTextToSize(entry.note, slipW - 8);
        const maxNoteLines = Math.min(noteLines.length, 3);
        for (let nl = 0; nl < maxNoteLines; nl++) {
          doc.text(noteLines[nl], left, cy + nl * LINE_H);
        }
      }
    } else {
      // ── OFFICER LAYOUT ──
      // L1: #ORDERNO (large, dark navy blue) ~ Name ~ BD-Number          R - X
      // L2: Item: <name>                                                  Branch
      // ── horizontal line ──
      // L3: numeric measurement values only, joined by " ~ "
      // L4: Shirt/Pant on left, delivery date on right
      const ORDER_FONT = FONT_SIZE + 4; // bigger order number
      const NAVY_R = 11, NAVY_G = 37, NAVY_B = 87;

      // ----- Line 1 -----
      setSlipFont();
      doc.setFontSize(ORDER_FONT);
      doc.setTextColor(NAVY_R, NAVY_G, NAVY_B);
      doc.text(orderNum, left, cy);
      const orderW = doc.getTextWidth(orderNum);

      // ribbon on the right (regular size)
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const ribbonW = ribonVal ? doc.getTextWidth(ribonVal) : 0;
      if (ribonVal) {
        doc.text(ribonVal, right, cy, { align: "right" });
      }

      // name + bd number in usual size next to the order number
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const nameBd = ` ~ ${customerName || "-"} ~ ${order.bd_number || "-"}`;
      const nameMaxW = right - left - orderW - (ribbonW ? ribbonW + 4 : 0);
      const nameTrunc = doc.splitTextToSize(nameBd, nameMaxW)[0] || nameBd;
      doc.text(nameTrunc, left + orderW, cy);

      // ----- Line 2 -----
      cy += LINE_H + 2;
      setSlipFont();
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const branchPart2 = branchOrTradeVal || "";
      const branchW = branchPart2 ? doc.getTextWidth(branchPart2) : 0;
      const itemText = `Item: ${entry.itemLabel}`;
      const itemMaxW = right - left - branchW - 4;
      const itemTrunc = doc.splitTextToSize(itemText, itemMaxW)[0] || itemText;
      doc.text(itemTrunc, left, cy);
      if (branchPart2) {
        doc.text(branchPart2, right, cy, { align: "right" });
      }

      // ----- Separator -----
      cy += LINE_H;
      doc.setLineWidth(0.4);
      doc.setDrawColor(0, 0, 0);
      doc.setLineDashPattern([], 0);
      doc.line(left, cy, right, cy);

      // ----- Measurements (values only) -----
      cy += LINE_H;
      setSlipFont();
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const measText = entry.measurements.map(([, value]) => value).join("  ~  ");
      const measLines = doc.splitTextToSize(measText, slipW - 8);
      doc.text(measLines, left, cy);
      cy += measLines.length * LINE_H;

      if (entry.note) {
        cy += 1;
        setSlipFont();
        doc.setFontSize(FONT_SIZE);
        doc.setTextColor(0, 0, 0);
        const noteLines = doc.splitTextToSize(entry.note, slipW - 8);
        const maxNoteLines = Math.min(noteLines.length, 3);
        for (let nl = 0; nl < maxNoteLines; nl++) {
          doc.text(noteLines[nl], left, cy + nl * LINE_H);
        }
        cy += maxNoteLines * LINE_H;
      }

      // ----- Bottom: type left, delivery date right -----
      cy += 2;
      setSlipFont();
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(NAVY_R, NAVY_G, NAVY_B);
      if (entry.type) {
        doc.text(entry.type, left, cy);
      }
      doc.setTextColor(0, 0, 0);
      if (deliveryDate) {
        doc.text(deliveryDate, right, cy, { align: "right" });
      }
    }
  };

  // Layout: 2 slips per row
  let col = 0;
  let currentY = margin;

  slipEntries.forEach((entry, idx) => {
    const slipH = FIXED_SLIP_H;

    // Check page overflow
    if (currentY + slipH > pageH - bottomMargin && currentY > margin) {
      if (col === 1) {
        col = 0;
      }
      doc.addPage();
      currentY = margin;
    }

    const xPos = col === 0 ? margin : margin + slipW + gap;
    renderSlip(xPos, currentY, entry, slipH);

    // No border around slips
    doc.setLineDashPattern([], 0);

    if (col === 0) {
      col = 1;
    } else {
      currentY += slipH + ROW_GAP;
      col = 0;
    }
  });

  // If ended on col=1 (odd number of slips), advance Y
  if (col === 1) {
    currentY += FIXED_SLIP_H + ROW_GAP;
  }

  openAndPrintPdf(doc);
}

export function generateAirmanSlipPDF(
  order: any,
  slipType: "shirt" | "pant",
  notes: string,
  customerName?: string,
  ivNumber?: string,
) {
  const doc = new jsPDF({ format: [80, 200], unit: "mm" });

  // Register Hind Siliguri Bold for Bengali text
  doc.addFileToVFS("HindSiliguri-Bold.ttf", hindSiliguriBoldBase64);
  doc.addFont("HindSiliguri-Bold.ttf", "HindSiliguri", "normal");
  doc.addFont("HindSiliguri-Bold.ttf", "HindSiliguri", "bold");

  const w = doc.internal.pageSize.getWidth();
  const left = 4;
  let y = 8;

  // Order info
  doc.setFontSize(9);
  doc.setFont("helvetica", "normal");
  const orderNum = order.order_number ? `#${order.order_number}` : `#${order.id.substring(0, 8)}`;
  doc.text(`Order No ${orderNum}`, left, y);

  y += 5;
  doc.text(`BD-Number: ${order.bd_number || "-"}`, left, y);

  y += 5;
  doc.text(`IV-Number: ${ivNumber || order.iv_number || "-"}`, left, y);

  // Item label
  y += 5;
  doc.setFont("helvetica", "bold");
  doc.text(`Item: ${slipType === "shirt" ? "Shirt" : "Pant"}`, left, y);

  // Separator
  y += 4;
  doc.setFont("helvetica", "normal");
  doc.setLineWidth(0.4);
  doc.setDrawColor(0, 0, 0);
  doc.line(left, y, w - left, y);

  // Measurement Notes
  y += 6;
  doc.setFont("helvetica", "bold");
  doc.setFontSize(9);
  doc.text("Measurement Notes:", left, y);
  y += 5;

  if (notes) {
    setDocFont(doc, notes, "normal");
    const lines = doc.splitTextToSize(notes, w - 10);
    doc.text(lines, left + 2, y);
  }

  openAndPrintPdf(doc);
}

export function generateBulkCuttingSlipPDF(
  ordersData: { order: any; items: any[]; customerName: string }[],
  categoryLabel: string,
) {
  const doc = new jsPDF({ orientation: "portrait", format: "a4", unit: "mm" });

  // Register Hind Siliguri Bold for Bengali text
  doc.addFileToVFS("HindSiliguri-Bold.ttf", hindSiliguriBoldBase64);
  doc.addFont("HindSiliguri-Bold.ttf", "HindSiliguri", "normal");
  doc.addFont("HindSiliguri-Bold.ttf", "HindSiliguri", "bold");

  const pageW = doc.internal.pageSize.getWidth();
  const pageH = doc.internal.pageSize.getHeight();
  const margin = 4;
  const gap = 4;
  const slipW = (pageW - margin * 2 - gap) / 2;
  const bottomMargin = 4;
  const ROWS_PER_PAGE = 7;
  const FIXED_SLIP_H = Math.floor((pageH - margin - bottomMargin) / ROWS_PER_PAGE);
  const FONT_SIZE = 12;
  const LINE_H = 4.6;

  const SHIRT_KEYS = ["length", "chest", "belly", "hip", "shoulder", "sleeve", "collar", "front", "back"];
  const PANT_KEYS = ["pant_length", "pant_waist", "pant_hip", "pant_high", "pant_thai", "pant_leg_open"];
  const DISPLAY_LABELS: Record<string, string> = {
    length: "Length", chest: "Chest", belly: "Belly", hip: "Hip", shoulder: "Shoulder",
    sleeve: "Sleeve", collar: "Collar", front: "Front", back: "Back",
    pant_length: "Length", pant_waist: "Waist", pant_hip: "Hip", pant_high: "High",
    pant_thai: "Thigh", pant_leg_open: "Leg Open",
  };

  type SlipEntry = {
    itemLabel: string;
    type: string;
    measurements: [string, string][];
    note: string;
    orderNum: string;
    customerName: string;
    bdNumber: string;
    branchOrTrade: string;
    ribonVal: string;
    deliveryDate: string;
    ivNumber: string;
    office: string;
    isAirman: boolean;
  };

  const allSlipEntries: SlipEntry[] = [];

  for (const { order, items, customerName } of ordersData) {
    const orderNum = order.order_number ? `#${order.order_number}` : `#${order.id.substring(0, 8)}`;
    const isAirman = order.order_category === "airman";
    const branchOrTrade = isAirman ? (order.trade || "") : (order.branch || "");
    const ribonVal = order.ribon != null ? `R - ${order.ribon}` : "";
    const deliveryDate = order.delivery_date || "";
    const shirtExtraNotes = order.extra_notes || "";
    const legacyMeasurementNotes = items.length === 1 && !items.some((i: any) => i?.notes) ? order.measurement_notes || "" : "";

    // Merge order-wide shared measurements as fallback for any item row missing values.
    const sharedMeas: Record<string, string> = (() => {
      const merged: Record<string, string> = {};
      items.forEach((it: any) => {
        const m = normalizeMeasurements(it?.measurements);
        Object.entries(m).forEach(([k, v]) => {
          const sv = typeof v === "string" ? v : v == null ? "" : String(v);
          if (sv && !merged[k]) merged[k] = sv;
        });
      });
      return merged;
    })();

    items.forEach((item: any, idx: number) => {
      const rawMeas = normalizeMeasurements(item.measurements) as Record<string, string>;
      const meas: Record<string, string> = {
        ...sharedMeas,
        ...Object.fromEntries(Object.entries(rawMeas).filter(([, v]) => v)),
      };

      const shirtMeas: [string, string][] = SHIRT_KEYS.map(k => [k, meas[k] || ""] as [string, string]).filter(([, v]) => v);
      const pantMeas: [string, string][] = PANT_KEYS.map(k => [k, meas[k] || ""] as [string, string]).filter(([, v]) => v);

      const itemLabel = item.item_name || (item.item_type ? item.item_type.charAt(0).toUpperCase() + item.item_type.slice(1) : "Item");
      const shirtHandNotes = item.notes || (idx === 0 ? legacyMeasurementNotes : "");
      const pantHandNotes = meas["__pant_notes"] || "";
      const pantExtraNotes = meas["__pant_extra_notes"] || "";
      const shirtCombinedNote = [shirtHandNotes, shirtExtraNotes].filter(Boolean).join("\n");
      const pantCombinedNote = [pantHandNotes, pantExtraNotes].filter(Boolean).join("\n");
      const ivNum = item.iv_number || order.iv_number || "";

      // Officer rows are stored one per quantity unit, so each item already produces one slip pair.
      // For airman items (which carry an aggregate quantity), expand by quantity here so each
      // piece prints its own slip. Officer rows: qty defaults to 1.
      const qty = isAirman ? Math.max(1, Number(item.quantity || 1)) : 1;
      const isAirmanFullSleeve = isAirman && (itemLabel || "").toLowerCase().includes("full sleeve");
      const OFFICER_SHIRT_ONLY_ITEMS = new Set([
        "half sleeve shirt", "full sleeve shirt", "punjabi", "jubba", "blazer",
        "meskit shirt", "koti", "dust coat", "mission genji", "fotua",
        "civil shirt", "half bush upor part",
      ]);
      const normalizeItemKey = (s: string) =>
        String(s || "").toLowerCase().replace(/[_-]+/g, " ").replace(/\s+/g, " ").trim();
      const itemKeys = [normalizeItemKey(itemLabel), normalizeItemKey(item.item_name), normalizeItemKey(item.item_type)];
      const isOfficerShirtOnly = !isAirman && itemKeys.some(k => k && OFFICER_SHIRT_ONLY_ITEMS.has(k));
      const OFFICER_PANT_ONLY_ITEMS = new Set([
        "uniform pant", "civil pant", "paijama",
      ]);
      const isOfficerPantOnly = !isAirman && itemKeys.some(k => k && OFFICER_PANT_ONLY_ITEMS.has(k));
      for (let q = 0; q < qty; q++) {
        if (shirtMeas.length > 0 && !isOfficerPantOnly) {
          allSlipEntries.push({ itemLabel, type: "Shirt", measurements: shirtMeas, note: shirtCombinedNote, orderNum, customerName, bdNumber: order.bd_number || "", branchOrTrade, ribonVal, deliveryDate, ivNumber: ivNum, office: order.office || "", isAirman });
        }
        if (pantMeas.length > 0 && !isAirmanFullSleeve && !isOfficerShirtOnly) {
          allSlipEntries.push({ itemLabel, type: "Pant", measurements: pantMeas, note: pantCombinedNote, orderNum, customerName, bdNumber: order.bd_number || "", branchOrTrade, ribonVal, deliveryDate, ivNumber: ivNum, office: order.office || "", isAirman });
        }
      }
    });
  }

  if (allSlipEntries.length === 0) {
    alert("No cutting slip data found for the selected items.");
    return;
  }

  const renderSlip = (x: number, y: number, entry: SlipEntry) => {
    const left = x + 4;
    const right = x + slipW - 4;
    let cy = y + 5;
    const setSlipFont = () => doc.setFont("HindSiliguri", "bold");

    if (entry.isAirman) {
      // ── AIRMAN LAYOUT (matches reference design) ──
      const ORDER_FONT = FONT_SIZE + 4;
      const NAVY_R = 11, NAVY_G = 37, NAVY_B = 87;

      // L1: ((#IVNUMBER)) (large, navy) ~ Name ~ BD-Number          R - X
      setSlipFont();
      doc.setFontSize(ORDER_FONT);
      doc.setTextColor(NAVY_R, NAVY_G, NAVY_B);
      const displayNum = entry.ivNumber ? `((#${entry.ivNumber}))` : `Order ${entry.orderNum}`;
      doc.text(displayNum, left, cy);
      const orderW = doc.getTextWidth(displayNum);

      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const ribbonW = entry.ribonVal ? doc.getTextWidth(entry.ribonVal) : 0;
      if (entry.ribonVal) {
        doc.text(entry.ribonVal, right, cy, { align: "right" });
      }

      const nameBd = ` ~ ${entry.customerName || "-"} ~ ${entry.bdNumber || "-"}`;
      const nameMaxW = right - left - orderW - (ribbonW ? ribbonW + 4 : 0);
      const nameTrunc = doc.splitTextToSize(nameBd, nameMaxW)[0] || nameBd;
      doc.text(nameTrunc, left + orderW, cy);

      // L2: Item:<name>[ (Pant)] | IV: <iv>
      cy += LINE_H + 2;
      setSlipFont();
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const itemSuffix = entry.type === "Pant" ? " (Pant)" : "";
      const itemLine = `Item:${entry.itemLabel}${itemSuffix}${entry.ivNumber ? ` | IV: ${entry.ivNumber}` : ""}`;
      const itemTrunc = doc.splitTextToSize(itemLine, slipW - 8)[0] || itemLine;
      doc.text(itemTrunc, left, cy);

      // Separator
      cy += LINE_H;
      doc.setLineWidth(0.4);
      doc.setDrawColor(0, 0, 0);
      doc.setLineDashPattern([], 0);
      doc.line(left, cy, right, cy);

      // Measurements (values only, form sort order)
      cy += LINE_H;
      setSlipFont();
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(0, 0, 0);
      const measText = entry.measurements.map(([, value]) => value).join("  ~  ");
      const measLines = doc.splitTextToSize(measText, slipW - 8);
      doc.text(measLines, left, cy);
      cy += measLines.length * LINE_H;

      if (entry.note) {
        cy += 1;
        doc.setFontSize(FONT_SIZE);
        setSlipFont();
        doc.setTextColor(0, 0, 0);
        const noteLines = doc.splitTextToSize(entry.note, slipW - 8);
        const maxNoteLines = Math.min(noteLines.length, 3);
        for (let nl = 0; nl < maxNoteLines; nl++) {
          doc.text(noteLines[nl], left, cy + nl * LINE_H);
        }
      }
    } else {
      // ── OFFICER LAYOUT ──
      // Row 1: #order ~ Name (Branch) ~ BD-Number
      doc.setFontSize(FONT_SIZE);
      setSlipFont();
      doc.setTextColor(30, 30, 30);
      doc.text(entry.orderNum, left, cy);

      const nameAndBd = `~ ${entry.customerName || "-"} ~ ${entry.bdNumber || "-"}`;
      const bigNumW = doc.getTextWidth(entry.orderNum + " ");
      doc.setFontSize(FONT_SIZE);
      setSlipFont();
      doc.setTextColor(50, 50, 50);
      const nameMaxW = right - left - bigNumW;
      const truncName = doc.splitTextToSize(nameAndBd, nameMaxW)[0] || nameAndBd;
      doc.text(truncName, left + bigNumW, cy);

      // Row 2: Item on left, Ribbon + Office on right
      cy += LINE_H + 1;
      doc.setFontSize(FONT_SIZE);
      setSlipFont();
      doc.setTextColor(30, 60, 100);
      const itemText = `Item: ${entry.itemLabel} (${entry.type})`;
      const itemTrunc = doc.splitTextToSize(itemText, (slipW - 8) * 0.6)[0] || itemText;
      doc.text(itemTrunc, left, cy);

      // Ribbon (R - X) goes on its own line ABOVE the branch / office line
      const branchOfficeLine = [entry.branchOrTrade, entry.office].filter(Boolean).join("  ");
      if (entry.ribonVal || branchOfficeLine) {
        setSlipFont();
        doc.setFontSize(FONT_SIZE);
        doc.setTextColor(50, 50, 50);
        if (entry.ribonVal && branchOfficeLine) {
          doc.text(entry.ribonVal, right, cy - LINE_H, { align: "right" });
          doc.text(branchOfficeLine, right, cy, { align: "right" });
        } else {
          doc.text(entry.ribonVal || branchOfficeLine, right, cy, { align: "right" });
        }
      }

      // Horizontal separator
      cy += LINE_H;
      doc.setLineWidth(0.4);
      doc.setDrawColor(0, 0, 0);
      doc.setLineDashPattern([], 0);
      doc.line(left, cy, right, cy);

      // Measurements
      cy += LINE_H;
      setSlipFont();
      doc.setFontSize(FONT_SIZE);
      doc.setTextColor(30, 30, 30);
      const labelPrefix = entry.type ? `${entry.type} ` : "";
      const measText = entry.measurements.map(([key, value]) => `${labelPrefix}${DISPLAY_LABELS[key] || key}: ${value}`).join("  ~  ");
      const measLines = doc.splitTextToSize(measText, slipW - 8);
      doc.text(measLines, left, cy);
      cy += measLines.length * LINE_H;

      if (entry.note) {
        cy += 1;
        doc.setFontSize(FONT_SIZE);
        setSlipFont();
        doc.setTextColor(30, 30, 30);
        const noteLines = doc.splitTextToSize(entry.note, slipW - 8);
        const maxNoteLines = Math.min(noteLines.length, 3);
        for (let nl = 0; nl < maxNoteLines; nl++) {
          doc.text(noteLines[nl], left, cy + nl * LINE_H);
        }
        cy += maxNoteLines * LINE_H;
      }

      // Footer: delivery date bottom right
      cy += 2;
      doc.setFontSize(FONT_SIZE);
      setSlipFont();
      doc.setTextColor(100, 100, 100);
      // Bottom line: item type on the left, delivery date on the right.
      if (entry.type) {
        doc.text(entry.type, left, cy);
      }
      if (entry.deliveryDate) {
        doc.text(entry.deliveryDate, right, cy, { align: "right" });
      }
    }
  };

  let col = 0;
  let currentY = margin;
  let isFirstPage = true;

  allSlipEntries.forEach((entry) => {
    if (currentY + FIXED_SLIP_H > pageH - bottomMargin && currentY > margin) {
      if (col === 1) col = 0;
      doc.addPage();
      currentY = margin;
    }

    const xPos = col === 0 ? margin : margin + slipW + gap;
    renderSlip(xPos, currentY, entry);

    doc.setLineDashPattern([], 0);

    if (col === 0) {
      col = 1;
    } else {
      currentY += FIXED_SLIP_H;
      col = 0;
    }
  });

  openAndPrintPdf(doc);
}
