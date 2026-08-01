import { createClient } from '@supabase/supabase-js';

// Server-only client (used exclusively inside app/api/** route handlers).
// SUPABASE_SERVICE_ROLE_KEY bypasses Row Level Security and must never be
// exposed to the browser — it has no NEXT_PUBLIC_ prefix on purpose.
//
// Lazily instantiated: Next.js imports route modules at build time to
// collect page data, before .env values are available, so creating the
// client at module load time throws "supabaseUrl is required".
let client;

export function getSupabase() {
  if (!client) {
    client = createClient(
      process.env.SUPABASE_URL,
      process.env.SUPABASE_SERVICE_ROLE_KEY,
      { auth: { persistSession: false } }
    );
  }
  return client;
}
