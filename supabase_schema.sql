-- Monthsary Gift Sanctuary: Supabase PostgreSQL schema
-- Run this entire script in the Supabase SQL Editor.
-- The PHP server must use SUPABASE_SERVICE_ROLE_KEY. Never expose that key in browser code.

create extension if not exists "uuid-ossp";

create table if not exists public.pets (
    id uuid primary key default uuid_generate_v4(),
    name text not null,
    breed_type text not null check (breed_type in ('white_heterochromia', 'tuxedo', 'gray_tabby')),
    avatar_url text,
    level integer not null default 1 check (level >= 1),
    exp integer not null default 0 check (exp >= 0),
    hunger integer not null default 100 check (hunger between 0 and 100),
    hygiene integer not null default 100 check (hygiene between 0 and 100),
    happiness integer not null default 100 check (happiness between 0 and 100),
    energy integer not null default 100 check (energy between 0 and 100),
    mood text not null default 'happy' check (mood in ('ecstatic', 'happy', 'sleepy', 'hungry', 'dirty', 'sad')),
    last_fed timestamptz not null default now(),
    last_bathed timestamptz not null default now(),
    last_petted timestamptz not null default now(),
    last_slept timestamptz not null default now(),
    created_at timestamptz not null default now()
);

create unique index if not exists pets_name_unique_idx on public.pets (name);
create index if not exists pets_breed_type_idx on public.pets (breed_type);

create table if not exists public.pet_logs (
    id uuid primary key default uuid_generate_v4(),
    pet_id uuid not null references public.pets(id) on delete cascade,
    action_type text not null check (action_type in ('feed', 'bath', 'pet', 'play', 'sleep')),
    stat_deltas jsonb not null default '{}'::jsonb,
    actor text not null default 'partner',
    created_at timestamptz not null default now()
);

create index if not exists pet_logs_pet_created_idx on public.pet_logs (pet_id, created_at desc);

create table if not exists public.gifts (
    id uuid primary key default uuid_generate_v4(),
    title text not null,
    letter_content text not null,
    polaroid_image_url text,
    audio_bg_url text,
    voice_note_url text,
    unlock_password_hash text,
    unlock_at timestamptz not null default '2026-09-14T00:00:00+08:00'::timestamptz,
    timeline_milestones jsonb not null default '[]'::jsonb check (jsonb_typeof(timeline_milestones) = 'array'),
    scratch_coupons jsonb not null default '[]'::jsonb check (jsonb_typeof(scratch_coupons) = 'array'),
    recipient_reaction text,
    recipient_note text,
    is_unlocked boolean not null default false,
    created_at timestamptz not null default now()
);

create index if not exists gifts_unlock_at_idx on public.gifts (unlock_at);
create index if not exists gifts_created_at_idx on public.gifts (created_at desc);

-- Seed the three guardians. ON CONFLICT makes this safe to run again.
insert into public.pets (name, breed_type, avatar_url, hunger, hygiene, happiness, energy, mood)
values
    ('Molly', 'white_heterochromia', 'assets/cats/molly.png', 90, 85, 95, 100, 'happy'),
    ('Mitch', 'tuxedo', 'assets/cats/mitch.png', 80, 95, 90, 85, 'happy'),
    ('Raica', 'gray_tabby', 'assets/cats/raica.png', 85, 80, 90, 90, 'happy')
on conflict (name) do nothing;

-- Lock the tables to the public API by default. The PHP service-role key bypasses RLS.
alter table public.pets enable row level security;
alter table public.pet_logs enable row level security;
alter table public.gifts enable row level security;

-- No anon/authenticated policies are created intentionally. All reads and writes go through PHP.
-- If browser clients are added later, create narrowly scoped authenticated policies instead of exposing service_role.
