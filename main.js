const API = {
  ideasCreate: '/api.php?action=create_idea',
  ideasListUrl: (params) => {
    const usp = new URLSearchParams(params);
    return `/api.php?action=list_ideas&${usp.toString()}`;
  },
  addLike: (id) => `/api.php?action=add_like&id=${encodeURIComponent(id)}`
};


function qs(sel, el = document) { return el.querySelector(sel); }

// Flags bootstrap for log silencing in prod
window.__TK_FLAGS = window.__TK_FLAGS || { dev: false };

function setThemeToggle() {
  const btn = qs('#themeToggle');
  if (!btn) return;
  btn.addEventListener('click', () => {
    const isDark = document.documentElement.classList.toggle('dark');
    btn.setAttribute('aria-pressed', String(isDark));
  });
}

async function submitIdea(e) {
  e.preventDefault();
  const form = e.currentTarget;
  const submitter_name = qs('#submitter_name').value.trim();
  const submitter_email = qs('#submitter_email').value.trim();
  const title = qs('#title').value.trim();
  const summary = qs('#summary').value.trim();
  const license_type = qs('#license_type').value.trim();
  const support_needs = qs('#support_needs').value.trim();
  const category = qs('#category')?.value.trim();
  const tagsRaw = qs('#tags')?.value.trim();
  const url = qs('#url')?.value.trim();
  const video_url = qs('#video_url')?.value.trim();
  const fileInput = qs('#attachment');
  const status = qs('#formStatus');
  const submitBtn = qs('#submitBtn');

  if (!submitter_name || !submitter_email || !title || !summary || !license_type) {
    status.textContent = 'Please complete all required fields.';
    return;
  }

  submitBtn.disabled = true;
  status.textContent = 'Submitting…';

  try {
    const formData = new FormData();
    formData.append('submitter_name', submitter_name);
    formData.append('submitter_email', submitter_email);
    formData.append('title', title);
    formData.append('summary', summary);
    formData.append('license_type', license_type);
    formData.append('support_needs', support_needs);
    if (category) formData.append('category', category);
    if (tagsRaw) {
      const normalized = normalizeTags(tagsRaw);
      if (normalized) formData.append('tags', normalized);
    }
    if (url) formData.append('url', url);
    if (video_url) formData.append('video_url', video_url);
    if (fileInput && fileInput.files[0]) {
      formData.append('attachment', fileInput.files[0]);
    }

    const res = await fetch(API.ideasCreate, { method: 'POST', body: formData });
    let data;
    const raw = await res.text();
    if (!res.ok) {
      // Surface raw server error (PHP/SQL) to UI
      throw new Error(raw || `HTTP ${res.status}`);
    }
    try {
      data = JSON.parse(raw);
    } catch (e) {
      throw new Error(`Invalid JSON from server: ${raw?.slice(0, 200) || '(empty)'}`);
    }
    const newId = data && (data.id || data.ID || data.new_id);
    status.textContent = '✅ Idea submitted. Redirecting…';
    setTimeout(() => (window.location.href = newId ? `/idea?id=${encodeURIComponent(newId)}` : '/'), 600);
    form.reset();
  } catch (err) {
    status.textContent = `❌ Submission failed: ${err?.message || 'Please try again.'}`;
    console.error('Submit error:', err);
  } finally {
    submitBtn.disabled = false;
  }
}

function mountForm() {
  const form = qs('#ideaForm');
  if (form) form.addEventListener('submit', submitIdea);
}

async function loadLeaderboard() {
  const grid = qs('#leaderboardGrid');
  const status = qs('#lbStatus');
  if (!grid) {
    if (window.__TK_FLAGS.dev) console.warn('Leaderboard grid element not found');
    return; // Not on the Leaderboard page
  }
  if (!status && window.__TK_FLAGS.dev) {
    console.warn('Status element not found');
  }

  // Parse category and pagination helpers
  const params = new URLSearchParams(location.search);
  let category = params.get('category') || '';
  if (!category) {
    const m = location.pathname.match(/^\/category\/(.+)$/);
    if (m) category = decodeURIComponent(m[1]);
  }
  const PAGE = 24;
  let offset = 0;

  async function fetchPage() {
    const url = API.ideasListUrl({ category, limit: PAGE, offset });
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const text = await res.text();
    if (!res.ok) {
      throw new Error(text || `HTTP ${res.status}`);
    }
    try {
      return JSON.parse(text);
    } catch (e) {
      const snippet = text.slice(0, 200);
      throw new Error(`Non-JSON response: ${snippet}`);
    }
  }

  status.textContent = 'Loading…';
  try {
    const data = await fetchPage();
    console.log('Fetched data:', data);

    // Clear grid and ensure it exists
    grid.innerHTML = '';
    console.log('Grid element:', grid, 'Grid classes:', grid.className);
    const sortedData = data.sort((a, b) => (b.tokens ?? 0) - (a.tokens ?? 0));
    const highestTokens = sortedData.length > 0 ? (sortedData[0].tokens ?? 0) : 0;

    sortedData.forEach((row, i) => {
      const cat = row.category || row.Category || 'Other';
      const tagsArr = Array.isArray(row.tags)
        ? row.tags
        : (row.tags ? normalizeTags(String(row.tags)).split(',') : []);

      // Check if this is truly rank #1 by tokens (handle ties)
      const isRankOne = (row.tokens ?? 0) === highestTokens && highestTokens > 0;

      const card = document.createElement('article');
      card.className = 'group relative tk-card';
      card.style.cssText = `
          margin-bottom: 1rem;
          border-radius: var(--tk-radius, 0.75rem);
          border: 1px solid var(--tk-border, #24324a);
          background-color: var(--tk-card, #11162a);
          box-shadow: var(--tk-shadow-sm, 0 1px 2px 0 rgba(0, 0, 0, 0.05));
          overflow: hidden;
          transition: all var(--tk-duration-normal, 250ms) var(--tk-ease-out, cubic-bezier(0.16, 1, 0.3, 1));
        `;

      const title = escapeHtml(row.title);
      const summary = escapeHtml(row.summary ?? '');

      card.innerHTML = `
  <div class="tk-card-layout">
    <!-- Top Stats Section (Mobile) / Left Stats Section (Desktop) -->
    <div class="tk-card-stats">
      <div class="tk-rank-badge ${isRankOne ? 'tk-rank-first' : ''}">
        ${isRankOne ? '<i class="iconoir-star-solid"></i>' : i + 1}
      </div>
      <div class="tk-stats-tokens">
        <div class="tk-stats-label"><i class="iconoir-coins"></i> AI Tokens</div>
        <div class="tk-stats-value">${row.tokens ?? 0}</div>
      </div>
      <div class="tk-stats-likes">
        <i class="iconoir-heart"></i> ${row.likes ?? 0} likes
      </div>
    </div>

    <!-- Content Section -->
    <div class="tk-card-content">
      <div class="tk-card-header">
        <div class="tk-card-title-wrapper">
          <h3 class="tk-card-title">
            <a href="/idea?id=${encodeURIComponent(row.id)}" class="stretched-link">${title}</a>
          </h3>
        </div>
        ${renderCategoryBadge(cat)}
      </div>

      <p class="tk-card-summary">${summary}</p>

      <div class="tk-card-footer">
        <div class="tk-card-tags">
          ${renderTagsChips(tagsArr)}
        </div>
        <button data-id="${row.id}" onclick="event.stopPropagation()" class="tk-btn tk-btn-secondary tk-card-like-btn">
          <i class="iconoir-heart"></i> Like
        </button>
      </div>
    </div>
  </div>
`;
      grid.appendChild(card);
    });

    offset += data.length;
    let more = data.length === PAGE;

    if (more) {
      const loadMoreBtn = document.createElement('button');
      loadMoreBtn.style.cssText = 'margin-top: 1.5rem; margin-left: auto; margin-right: auto; display: block; padding: 0.75rem 2rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.375rem; border: 1px solid #e4e4e7; background-color: #ffffff; color: #18181b; font-weight: 600; cursor: pointer; transition: all 0.15s ease-in-out;';
      loadMoreBtn.textContent = 'Load more ideas';
      grid.parentElement.appendChild(loadMoreBtn);

      loadMoreBtn.addEventListener('click', async () => {
        loadMoreBtn.disabled = true;
        loadMoreBtn.textContent = 'Loading...';
        try {
          const next = await fetchPage();
          const nextSorted = next.sort((a, b) => (b.tokens ?? 0) - (a.tokens ?? 0));

          nextSorted.forEach((row, i) => {
            const cat = row.category || row.Category || 'Other';
            const tagsArr = Array.isArray(row.tags)
              ? row.tags
              : (row.tags ? normalizeTags(String(row.tags)).split(',') : []);
            const card = document.createElement('article');
            card.className = 'group relative';
            card.style.cssText = `
                margin-bottom: 1rem;
                border-radius: 0.75rem;
                border: 1px solid #e4e4e7;
                background-color: #ffffff;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                overflow: hidden;
                transition: all 0.15s ease-in-out;
              `;
            const title = escapeHtml(row.title);
            const summary = escapeHtml(row.summary ?? '');
            const currentIndex = offset + i + 1;
            // For load more, we need to check if this matches the overall highest tokens from the entire dataset
            const isRankOne = (row.tokens ?? 0) === highestTokens && highestTokens > 0;
            card.innerHTML = `
  <div style="display: flex;">
    <!-- Left Stats Section -->
    <div style="flex-shrink: 0; width: 12rem; background: linear-gradient(to bottom right, ${isRankOne ? '#c8ffc5, #60fff1' : '#f0fdf4, #dcfce7'}); padding: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; border-right: 1px solid ${isRankOne ? '#60fff1' : '#bbf7d0'};">
      <div style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 50%; background-color: ${isRankOne ? '#01564e' : '#16a34a'}; color: white; font-size: 0.875rem; font-weight: bold; margin-bottom: 0.75rem; ${isRankOne ? 'box-shadow: 0 0 0 2px #c8ffc5;' : ''}">
        ${isRankOne ? '👑' : currentIndex}
      </div>
      <div style="margin-bottom: 1rem;">
        <div style="font-size: 0.75rem; font-weight: 600; color: ${isRankOne ? '#01564e' : '#16a34a'}; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">🍀 Tokens</div>
        <div style="font-size: 1.5rem; font-weight: bold; color: ${isRankOne ? '#01564e' : '#15803d'};">${row.tokens ?? 0}</div>
      </div>
      <div style="font-size: 0.75rem; color: ${isRankOne ? '#01564e' : '#16a34a'};">
        ${row.likes ?? 0} likes
      </div>
    </div>

    <!-- Right Content Section -->
    <div style="flex: 1; padding: 1.5rem;">
      <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem;">
        <div style="flex: 1;">
          <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">
            <a href="/idea?id=${encodeURIComponent(row.id)}" class="stretched-link">${title}</a>
          </h3>
        </div>
        ${renderCategoryBadge(cat)}
      </div>

      <p style="color: var(--tk-muted, #9fb3c8); font-size: 0.875rem; line-height: 1.625; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">${summary}</p>

      <div style="display: flex; align-items: center; justify-content: space-between;">
        <div style="flex: 1;">
          ${renderTagsChips(tagsArr)}
        </div>
        <button data-id="${row.id}" onclick="event.stopPropagation()" class="tk-btn tk-btn-secondary" style="margin-left: 1rem; position: relative; z-index: 10; min-height: 44px; display: flex; align-items: center; gap: 0.5rem;"><i class="iconoir-heart"></i> Like</button>
      </div>
    </div>
  </div>
`;
            grid.appendChild(card);
          });
          offset += next.length;
          if (next.length < PAGE) {
            loadMoreBtn.remove();
          } else {
            loadMoreBtn.textContent = 'Load more ideas';
            loadMoreBtn.disabled = false;
          }
        } catch (err) {
          status.textContent = `❌ Load error: ${err?.message}`;
          loadMoreBtn.textContent = 'Load more ideas';
          loadMoreBtn.disabled = false;
        }
      });
    }

    grid.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-id]');
      if (!btn) return;
      btn.disabled = true;
      try {
        const id = btn.getAttribute('data-id');
        const res = await fetch(API.addLike(id), { method: 'POST' });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        await loadLeaderboard(); // refresh
      } catch (err) {
        console.error(err);
        status.textContent = '❌ Could not add like.';
      } finally {
        btn.disabled = false;
      }
    });


    status.textContent = data.length ? '' : 'No ideas yet.';
  } catch (err) {
    console.error(err);
    status.textContent = '❌ Failed to load leaderboard.';
  }
}

function escapeHtml(s) {
  return String(s).replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
}

function normalizeTags(input) {
  return String(input)
    .split(',')
    .map(t => t.trim().toLowerCase())
    .filter(t => t.length)
    .filter((t, i, arr) => arr.indexOf(t) === i)
    .join(',');
}

function renderCategoryBadge(cat) {
  const c = cat ? String(cat) : 'Other';
  return `<span class="tk-badge tk-badge-success" style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;" aria-label="category">${escapeHtml(c)}</span>`;
}

function renderTagsChips(tags) {
  const arr = (Array.isArray(tags) ? tags : []).map(t => String(t).trim()).filter(Boolean);
  if (!arr.length) return '';

  // Split tags by both commas and spaces, then clean them up and remove # prefix
  const allTags = arr.flatMap(tagStr =>
    tagStr.split(/[,\s]+/).map(t => t.trim().replace(/^#/, '')).filter(Boolean)
  );

  const chips = allTags.map(t =>
    `<span class="tk-badge tk-badge-muted" style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; margin-right: 0.5rem; margin-bottom: 0.5rem;" aria-label="tag">${escapeHtml(t)}</span>`
  ).join('');
  return `<div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">${chips}</div>`;
}

// Initialize Tindlekit UI System
import('./ui/tindlekit.js').then(({ default: Tindlekit }) => {
  // Tindlekit will auto-initialize, but we can listen for ready event
  window.addEventListener('tindlekit:ready', () => {
    console.log('🎨 Tindlekit UI enhancements active');
  });
});

setThemeToggle();
mountForm();
if (document.getElementById('leaderboardGrid')) {
  loadLeaderboard();
}
