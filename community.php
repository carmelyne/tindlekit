<!doctype html>
<html lang="en" class="h-full">

  <?php include 'includes/head.php'; ?>

  <body class="h-full">
    <!-- Three.js background container -->
    <div id="app-bg"></div>
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-4xl px-4 py-8">
      <!-- Hero Section -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4 text-tk-fg">
          <i class="iconoir-group"> </i> Community Support
        </h1>
        <p class="text-xl text-tk-muted max-w-3xl mx-auto">
          Learn how Tindlekit works, discover the power of AI Tokens, and join our mission to support human innovation.
        </p>
      </div>

      <!-- Origin Story Section -->
      <section class="tk-card p-8 mb-12">
        <h2 class="text-2xl font-semibold mb-6 text-tk-fg">Our Origin Story</h2>
        <div class="prose prose-invert max-w-none">
          <p class="text-tk-muted leading-relaxed mb-4">
            Tindlekit was born from a simple observation: brilliant ideas often die not from lack of merit, but from
            lack of visibility and early support. In our hyper-connected world, genuinely transformative concepts can
            get lost in the noise.
          </p>
          <p class="text-tk-muted leading-relaxed mb-4">
            We created Tindlekit as a signal board where the community can identify, rally around, and tangibly support
            ideas that matter. By combining transparent momentum tracking with meaningful pledge types—AI Tokens, Time,
            and Mentorship—we're making inspiration and coordination tangible.
          </p>
          <p class="text-tk-muted leading-relaxed">
            Built specifically for <a href="https://x.com/karpathy/status/1952076108565991588"
              class="text-tk-accent hover:underline" target="_blank" rel="noopener">Andrej Karpathy's
              PayoutChallenge</a>, Tindlekit represents our contribution to uplifting "team human" through better
            coordination and support mechanisms.
          </p>
        </div>
      </section>

      <!-- How AI Tokens Work -->
      <section class="tk-card p-8 mb-12">
        <h2 class="text-2xl font-semibold mb-6 text-tk-fg">
          <span class="tk-badge tk-badge-success mr-3"><i class="iconoir-vegan-circle"> </i></span>
          How AI Tokens Work
        </h2>
        <div class="grid md:grid-cols-2 gap-8">
          <div>
            <h3 class="text-lg font-semibold mb-3 text-tk-fg">What Are AI Tokens?</h3>
            <p class="text-tk-muted leading-relaxed">
              AI Tokens represent convertible credits for AI API and compute costs. When you pledge tokens to an idea,
              you're essentially saying "I believe this is worth funding with AI resources."
            </p>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-3 text-tk-fg">Real Impact</h3>
            <p class="text-tk-muted leading-relaxed">
              Example: "We pooled 10B tokens to fund this idea's AI development costs." These aren't just votes—they
              represent actual resource commitments.
            </p>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-3 text-tk-fg">Transparent Momentum</h3>
            <p class="text-tk-muted leading-relaxed">
              Every pledge is public, creating clear signals about which ideas have community backing and reducing
              coordination problems.
            </p>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-3 text-tk-fg">Three Support Types</h3>
            <div class="space-y-2">
              <div class="tk-badge tk-badge-success"><i class="iconoir-vegan-circle"></i> AI Tokens</div>
              <div class="tk-badge tk-badge-muted"><i class="iconoir-vegan-circle text-tk-accent"></i> Time</div>
              <div class="tk-badge tk-badge-muted"><i class="iconoir-strategy text-tk-accent"></i> Mentorship</div>
            </div>
          </div>
        </div>
      </section>

      <!-- FAQ Section -->
      <section class="tk-card p-8 mb-12">
        <h2 class="text-2xl font-semibold mb-6 text-tk-fg">Frequently Asked Questions</h2>
        <div class="space-y-6">

          <div class="border-b border-tk-border pb-6">
            <h3 class="text-lg font-semibold mb-2 text-tk-fg">How do I submit an idea?</h3>
            <p class="text-tk-muted">
              Click "Submit an Idea" in the navigation. Fill out the form with your idea's title, summary, category, and
              any supporting links or files. All submissions are public and immediately available for community support.
            </p>
          </div>

          <div class="border-b border-tk-border pb-6">
            <h3 class="text-lg font-semibold mb-2 text-tk-fg">What makes a good idea for Tindlekit?</h3>
            <p class="text-tk-muted">
              Ideas that could benefit humanity through AI/technology, have clear potential impact, and could use
              community coordination. Think open source projects, research initiatives, tools that solve real problems,
              or platforms that connect people.
            </p>
          </div>

          <div class="border-b border-tk-border pb-6">
            <h3 class="text-lg font-semibold mb-2 text-tk-fg">Are pledges binding commitments?</h3>
            <p class="text-tk-muted">
              Pledges are expressions of intent and community interest. They create transparency about which ideas have
              support, helping creators understand demand and potential collaborators find each other.
            </p>
          </div>

          <div class="border-b border-tk-border pb-6">
            <h3 class="text-lg font-semibold mb-2 text-tk-fg">Can I run my own challenge or initiative?</h3>
            <p class="text-tk-muted">
              Absolutely! Tindlekit is evergreen and accepts ideas continuously. While we're highlighting the
              PayoutChallenge now, the platform is designed to support any community-driven initiative or individual
              project.
            </p>
          </div>

          <div class="pb-6">
            <h3 class="text-lg font-semibold mb-2 text-tk-fg">How does investor interest work?</h3>
            <p class="text-tk-muted">
              Investors and funders can express interest in ideas without affecting AI Token counts. This creates an
              additional signal for creators about potential funding opportunities while keeping community support
              separate from capital interests.
            </p>
          </div>

        </div>
      </section>

      <!-- Safety & Guidelines -->
      <section class="tk-card p-8 mb-12">
        <h2 class="text-2xl font-semibold mb-6 text-tk-fg">Safety & Guidelines</h2>
        <div class="grid md:grid-cols-2 gap-8">
          <div>
            <h3 class="text-lg font-semibold mb-3 text-tk-fg">Community Standards</h3>
            <ul class="text-tk-muted space-y-2">
              <li>• Ideas should aim to benefit humanity</li>
              <li>• Respectful, constructive engagement only</li>
              <li>• No spam, self-promotion, or misleading content</li>
              <li>• Original ideas or clear attribution required</li>
            </ul>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-3 text-tk-fg">Privacy & Security</h3>
            <ul class="text-tk-muted space-y-2">
              <li>• All submissions and interactions are public</li>
              <li>• Email addresses are used for notifications only</li>
              <li>• No sensitive personal information should be shared</li>
              <li>• Report inappropriate content to moderators</li>
            </ul>
          </div>
        </div>
      </section>

      <!-- Call to Action -->
      <section class="text-center py-12">
        <h2 class="text-3xl font-bold mb-4 text-tk-fg">Ready to Make an Impact?</h2>
        <p class="text-tk-muted mb-8 max-w-2xl mx-auto">
          Join our community of innovators, supporters, and builders. Whether you have an idea to share or want to
          support others, every contribution matters.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="/" class="tk-btn tk-btn-primary">
            Browse Ideas
          </a>
          <a href="/submit-idea" class="tk-btn tk-btn-secondary">
            Submit Your Idea
          </a>
        </div>
      </section>

    </main>

    <script type="module" src="/main.js"></script>
    <?php include 'includes/footer.php'; ?>
  </body>

</html>
