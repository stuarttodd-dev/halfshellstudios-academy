<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class EmailTemplate
{
    public function __construct(
        public readonly string $subject,
        public readonly string $headerHtml,
        public readonly string $bodyHtml,
        public readonly string $footerHtml,
        public readonly string $fontFamily,
        public readonly ?string $recipientName = null,
    ) {}

    public function withRecipientName(string $name): self
    {
        return new self(
            subject: $this->subject,
            headerHtml: $this->headerHtml,
            bodyHtml: $this->bodyHtml,
            footerHtml: $this->footerHtml,
            fontFamily: $this->fontFamily,
            recipientName: $name,
        );
    }

    public function bodyForRecipient(): string
    {
        return $this->recipientName === null
            ? $this->bodyHtml
            : str_replace('{{name}}', $this->recipientName, $this->bodyHtml);
    }
}

final class EmailTemplateRegistry
{
    /** @var array<string, EmailTemplate> */
    private array $templates = [];

    public function register(string $name, EmailTemplate $template): void { $this->templates[$name] = $template; }

    public function get(string $name): EmailTemplate
    {
        $proto = $this->templates[$name] ?? throw new \RuntimeException("unknown template {$name}");
        return clone $proto;
    }
}

// ---- assertions -------------------------------------------------------------

$registry = new EmailTemplateRegistry();
$registry->register('welcome', new EmailTemplate(
    subject: 'Welcome aboard',
    headerHtml: '<header>Half Shell</header>',
    bodyHtml: '<p>Hi {{name}}, welcome.</p>',
    footerHtml: '<footer>Sent with care.</footer>',
    fontFamily: 'Inter',
));
$registry->register('password-reset', new EmailTemplate(
    subject: 'Password reset',
    headerHtml: '<header>Half Shell</header>',
    bodyHtml: '<p>Hi {{name}}, click the link.</p>',
    footerHtml: '<footer>Sent with care.</footer>',
    fontFamily: 'Inter',
));

$emailForSam = $registry->get('welcome')->withRecipientName('Sam');
$emailForJo  = $registry->get('welcome')->withRecipientName('Jo');

pdp_assert_eq('<p>Hi Sam, welcome.</p>', $emailForSam->bodyForRecipient(), 'sam variant');
pdp_assert_eq('<p>Hi Jo, welcome.</p>',  $emailForJo->bodyForRecipient(),  'jo variant');
pdp_assert_eq('Welcome aboard', $emailForSam->subject, 'subject preserved by clone');

// independence
pdp_assert_eq('Sam', $emailForSam->recipientName, 'sam name set');
pdp_assert_eq('Jo',  $emailForJo->recipientName,  'jo name set');
pdp_assert_true($emailForSam !== $emailForJo, 'clones are distinct objects');

// originals untouched
pdp_assert_eq(null, $registry->get('welcome')->recipientName, 'registry prototype unchanged after with*');

pdp_done();
