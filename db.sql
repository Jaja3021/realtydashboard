-- Realty Dashboard schema (Postgres / Supabase)
-- Run this in the Supabase SQL Editor (Project → SQL Editor → New query).
--
-- This version replaces the old single `buyer_name` column with split
-- family/first name fields and adds phase/block/lot/assisting_name. If you
-- already created the table from an earlier version of this file, this drops
-- it first — only run this if it's fine to lose any rows currently in it.
DROP TABLE IF EXISTS sales;

CREATE TABLE IF NOT EXISTS sales (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    property_name       TEXT NOT NULL,
    property_type       TEXT NOT NULL,
    location            TEXT,
    phase               TEXT,
    block               TEXT NOT NULL,
    lot                 TEXT NOT NULL,
    buyer_family_name   TEXT NOT NULL,
    buyer_first_name    TEXT NOT NULL,
    buyer_contact       TEXT,
    price               NUMERIC(15,2) NOT NULL,
    agent_name          TEXT NOT NULL,
    assisting_name      TEXT,
    date_sold           DATE NOT NULL,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- RLS is on by default for new Supabase tables with no policies, which blocks
-- all access. That's fine here: the app only ever talks to this table through
-- the Next.js API routes using the service_role key, which bypasses RLS.
ALTER TABLE sales ENABLE ROW LEVEL SECURITY;

-- Sample data so the dashboard has something to show on first load.
-- Feel free to delete these rows from the Sales list once you add real ones.
INSERT INTO sales (property_name, property_type, location, phase, block, lot, buyer_family_name, buyer_first_name, buyer_contact, price, agent_name, assisting_name, date_sold) VALUES
('Sunrise Villas',            'House and Lot', 'Cavite',      'Phase 1', '3',  '12', 'Santos',   'Maria',     '0917-111-2222', 4500000.00, 'Jenny Cruz',     NULL,          '2026-07-03'),
('Greenfield Residences #402','Condominium',   'Quezon City', NULL,      'N/A','402','Reyes',    'Paolo',     '0918-222-3333', 3200000.00, 'Mark Villanueva','Liza Robles', '2026-07-08'),
('Palm Estates',              'Lot Only',      'Batangas',    NULL,      '2',  '7',  'Lim',      'Ana',       '0919-333-4444',  980000.00, 'Jenny Cruz',     NULL,          '2026-07-15'),
('The Meadows Townhome',      'Townhouse',     'Laguna',      NULL,      'C',  '1',  'Dizon',    'Carlo',     '0920-444-5555', 2750000.00, 'Rico Tan',       NULL,          '2026-07-19'),
('Skyline Tower Unit 1801',   'Condominium',   'Makati',      NULL,      'N/A','1801','Uy',      'Grace',     '0921-555-6666', 6100000.00, 'Mark Villanueva','Rico Tan',    '2026-07-22'),
('Sunrise Villas',            'House and Lot', 'Cavite',      'Phase 1', '1',  '5',  'Chua',     'Ferdinand', '0922-666-7777', 4300000.00, 'Jenny Cruz',     NULL,          '2026-07-25'),
('Commercial Space',          'Commercial',    'Pasig',       NULL,      'G',  '2',  'Robles',   'Liza',      '0923-777-8888', 8900000.00, 'Rico Tan',       NULL,          '2026-06-10'),
('Greenfield Residences #210','Condominium',   'Quezon City', NULL,      'N/A','210','Aquino',   'Noel',      '0924-888-9999', 3100000.00, 'Mark Villanueva',NULL,          '2026-06-18');

-- Sidebar calendar: meetings, site trippings, and duty/manning schedules.
CREATE TABLE IF NOT EXISTS events (
    id          BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    event_date  DATE NOT NULL,
    event_type  TEXT NOT NULL CHECK (event_type IN ('meeting', 'tripping', 'manning', 'pks')),
    title       TEXT NOT NULL,
    notes       TEXT,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

ALTER TABLE events ENABLE ROW LEVEL SECURITY;
