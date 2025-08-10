<?php
// about.php — About page for Tindlekit: Inspired by The Andrej Effect
?>
<!doctype html>
<html lang="en">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>About — Tindlekit: Inspired by The Andrej Effect</title>
    <link rel="icon" href="/favicon.ico" />
    <link rel="stylesheet" href="/styles.css?v=<?= time() ?>" />
    <meta name="description" content="Why the Idea Commons exists, how AI Tokens work, and how to participate." />
  </head>

  <body class="bg-white text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-5xl px-4 py-8">
      <!-- Breadcrumb / Back -->
      <p class="mb-4 text-sm"><a href="/" class="underline">← Back to Leaderboard</a></p>

      <!-- Hero -->
      <header class="mb-6">
        <h1 class="text-3xl font-bold mb-2">About the Idea Commons</h1>
        <p class="muted max-w-3xl">Tindlekit: <em>Inspired by The Andrej Effect</em> is an open platform where bold
          ideas of all kinds can take root. We use <strong>AI Tokens</strong> as a simple, transparent way to pledge
          support so promising ideas get momentum early.</p>
      </header>

      <!-- Origin Story -->
      <section class="card mb-6">
        <h2 class="text-xl font-semibold mb-2">Origin Story</h2>
        <p class="mb-3">We noticed a gap: traditional crowdfunding focuses on finished campaigns, while many impactful
          ideas need help <em>much earlier</em>—at the sketch, document, or prototype stage. The “Andrej Effect” is our
          shorthand for the surge of thoughtful, community-driven innovation we’ve seen across AI. This platform is a
          small way to amplify that energy—with respect and attribution—without claiming endorsement.</p>
        <p class="muted">We’re building a commons where the community can quickly spot promising concepts, offer help,
          and keep progress visible.</p>
      </section>

      <!-- How it Works -->
      <section class="card mb-6">
        <h2 class="text-xl font-semibold mb-2">How AI Tokens Work</h2>
        <ul class="list-disc pl-5 space-y-2">
          <li><strong>Pledges, not purchases.</strong> AI Tokens are a unit of support you pledge to an idea, typically earmarked for API/compute credits so builders can create with AI. They’re not a payment rail; they’re a clear signal of commitment, momentum, and resource intent. During this MVP/proof-of-concept stage, no purchases, donations, or financial transactions occur on-platform.</li>
          <li><strong>Two signals:</strong> quick <em>Likes</em> for discovery, and <em>Token pledges</em> for serious
            backing. Tokens appear as the prominent total on each idea page.</li>
          <li><strong>Contributors count:</strong> each unique supporter (time, mentorship, or token pledge) increases
            the contributor total.</li>
          <li><strong>Categories & tags:</strong> ideas are organized by a curated category and free-form tags to help
            people find and join efforts faster.</li>
        </ul>
        <p class="muted mt-3">We also show an aspirational USD mapping per token in some views—purely as context. Actual
          transactions (if any) are between contributors and creators off-platform. In many cases, pledged tokens correspond to real-world API/compute credits provided off-platform once projects reach agreed criteria.</p>
      </section>

      <!-- What makes it different -->
      <section class="card mb-6">
        <h2 class="text-xl font-semibold mb-2">What Makes This Different</h2>
        <ul class="list-disc pl-5 space-y-2">
          <li><strong>Earliest-stage friendly.</strong> Submit rough ideas, concept docs, or small prototypes. The bar
            is “useful intention,” not glossy production.</li>
          <li><strong>Open participation.</strong> Contributors can pledge tokens, time, or mentorship—and get connected
            to projects that need help.</li>
          <li><strong>Transparent momentum.</strong> We show token totals and contributors up-front so others can
            quickly assess traction.</li>
          <li><strong>Respectful framing.</strong> “Inspired by The Andrej Effect” signals homage, not affiliation or
            endorsement.</li>
        </ul>
      </section>

      <!-- Values / Safety -->
      <section class="card mb-6">
        <h2 class="text-xl font-semibold mb-2">Values & Safety</h2>
        <ul class="list-disc pl-5 space-y-2">
          <li><strong>Do what’s right.</strong> We can’t live a beautiful life and do ugly things to other people.
            Projects that harm others don’t belong here.</li>
          <li><strong>Open by default.</strong> Prefer permissive licenses where feasible; clearly mark constraints if
            not.</li>
          <li><strong>Transparent intent.</strong> State what you need: feedback, contributors, mentorship, compute, or
            visibility.</li>
          <li><strong>Consent & attribution.</strong> Give credit. Don’t use names, brands, or likenesses in confusing
            ways.</li>
        </ul>
      </section>

      <!-- FAQ (short) -->
      <section class="card mb-6">
        <h2 class="text-xl font-semibold mb-2">FAQ (Short)</h2>
        <div class="space-y-3">
          <details class="group">
            <summary class="cursor-pointer font-medium">Are AI Tokens a cryptocurrency?</summary>
            <p class="mt-2 muted">No. They’re a <em>pledge unit</em> we track to indicate support and momentum. They are
              not a coin, security, or payment token.</p>
          </details>
          <details class="group">
            <summary class="cursor-pointer font-medium">How do likes relate to tokens?</summary>
            <p class="mt-2 muted">Likes are lightweight discovery signals; token pledges are deliberate contributions.
              We show them separately.</p>
          </details>
          <details class="group">
            <summary class="cursor-pointer font-medium">Can I change or withdraw a pledge?</summary>
            <p class="mt-2 muted">Pledges express intent. If circumstances change, contact the project owner directly.
              We don’t process money on-platform.</p>
          </details>
        </div>
      </section>

      <!-- CTA -->
      <section class="mb-10">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
          <h2 class="text-lg font-semibold mb-2">Join the Commons</h2>
          <p class="muted mb-3">Have an idea that can help humanity move forward with AI? Plant it in the commons—or
            back one that inspires you.</p>
          <div class="flex flex-wrap gap-2">
            <a href="/submit-idea" class="btn btn-primary">Submit an Idea</a>
            <a href="/" class="btn btn-secondary">Browse the Leaderboard</a>
          </div>
        </div>
      </section>

      <p class="muted text-xs pt-6">Last updated: <?php echo date('F j, Y'); ?></p>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script type="module" src="/main.js"></script>
  </body>

</html>
