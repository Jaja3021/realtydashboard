import { NextResponse } from 'next/server';
import { getSupabase } from '@/lib/supabase';

export const dynamic = 'force-dynamic';

function isoDate(d) {
  return d.toISOString().slice(0, 10);
}

export async function GET() {
  const supabase = getSupabase();
  const { data: sales, error } = await supabase.from('sales').select('*');
  if (error) return NextResponse.json({ error: error.message }, { status: 500 });

  const now = new Date();
  const monthStart = isoDate(new Date(now.getFullYear(), now.getMonth(), 1));
  const nextMonthStart = isoDate(new Date(now.getFullYear(), now.getMonth() + 1, 1));

  const thisMonthSales = sales.filter((s) => s.date_sold >= monthStart && s.date_sold < nextMonthStart);
  const monthStat = {
    units: thisMonthSales.length,
    revenue: thisMonthSales.reduce((sum, s) => sum + Number(s.price), 0),
  };

  const allStat = {
    units: sales.length,
    revenue: sales.reduce((sum, s) => sum + Number(s.price), 0),
  };
  allStat.avgPrice = allStat.units ? allStat.revenue / allStat.units : 0;

  const agentTotals = new Map();
  for (const s of thisMonthSales) {
    const cur = agentTotals.get(s.agent_name) || { agent_name: s.agent_name, units: 0, revenue: 0 };
    cur.units += 1;
    cur.revenue += Number(s.price);
    agentTotals.set(s.agent_name, cur);
  }
  const topAgent = [...agentTotals.values()].sort((a, b) => b.revenue - a.revenue)[0] || null;

  const trend = [];
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const ym = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
    const label = d.toLocaleString('en-US', { month: 'short', year: 'numeric' });
    const revenue = sales
      .filter((s) => s.date_sold.startsWith(ym))
      .reduce((sum, s) => sum + Number(s.price), 0);
    trend.push({ label, revenue });
  }

  const typeTotals = new Map();
  for (const s of sales) {
    const cur = typeTotals.get(s.property_type) || { property_type: s.property_type, units: 0, revenue: 0 };
    cur.units += 1;
    cur.revenue += Number(s.price);
    typeTotals.set(s.property_type, cur);
  }
  const typeBreakdown = [...typeTotals.values()].sort((a, b) => b.revenue - a.revenue);
  const bestType = [...typeTotals.values()].sort((a, b) => b.units - a.units)[0] || null;

  const recent = [...sales]
    .sort((a, b) => (b.date_sold < a.date_sold ? -1 : b.date_sold > a.date_sold ? 1 : b.id - a.id))
    .slice(0, 6);

  return NextResponse.json({ monthStat, allStat, topAgent, bestType, trend, typeBreakdown, recent });
}
