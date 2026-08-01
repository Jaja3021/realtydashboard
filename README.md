# Realty Dashboard

A real estate sales dashboard: analytics overview, a form for logging sold
properties, and a monthly top-sales leaderboard by agent.

## Stack

- **Frontend:** plain HTML/CSS/JS, served as static files from `public/`
- **Backend:** Next.js API routes (`app/api/**`)
- **Database:** Supabase (Postgres)

## Local setup

1. `npm install`
2. Create a Supabase project at [supabase.com](https://supabase.com), then run
   [db.sql](db.sql) in its SQL Editor to create the `sales` table and seed
   sample data.
3. Copy `.env.local.example` to `.env.local` and fill in your Supabase
   project's URL and **service_role** key (Project Settings → API).
   The service_role key is only ever used server-side in `app/api/**` route
   handlers — never expose it to the browser.
4. `npm run dev` and open `http://localhost:3000`.

## Pages

- `/` — dashboard (revenue trend, property-type breakdown, recent sales)
- `/add-sale` — form to log a sold property
- `/sales` — full sales list with search (buyer/property/agent) and type filter
- `/top-sales` — monthly agent leaderboard

## Deploying to Vercel

1. Push this repo to GitHub (already done if you're reading this from the repo).
2. On [vercel.com](https://vercel.com), **Add New Project** → import this repo.
3. In the project's **Settings → Environment Variables**, add `SUPABASE_URL`
   and `SUPABASE_SERVICE_ROLE_KEY` (same values as `.env.local`).
4. Deploy — Vercel builds the Next.js API routes as serverless functions and
   serves everything in `public/` as static assets automatically.
