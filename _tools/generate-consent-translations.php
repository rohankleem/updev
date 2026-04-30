<?php
/**
 * One-off CLI tool: generate .pot + .mo translation files for the consent popup strings.
 *
 * Uses WordPress's bundled POMO library (no full WP bootstrap required).
 * Output: public_html/wp-content/plugins/unipixel/languages/unipixel.pot + unipixel-{locale}.mo
 *
 * Run: C:/xampp/php/php.exe _tools/generate-consent-translations.php
 *
 * Re-run whenever strings change — idempotent (overwrites existing files).
 */

$repo_root  = dirname(__DIR__);
$pomo_dir   = $repo_root . '/public_html/wp-includes/pomo';
$lang_dir   = $repo_root . '/public_html/wp-content/plugins/unipixel/languages';

require_once $pomo_dir . '/streams.php';
require_once $pomo_dir . '/entry.php';
require_once $pomo_dir . '/translations.php';
require_once $pomo_dir . '/po.php';
require_once $pomo_dir . '/mo.php';

if (!is_dir($lang_dir)) {
    mkdir($lang_dir, 0755, true);
}

/**
 * Canonical English source strings. MUST stay in sync with unipixel_consent_string_defaults()
 * in plugins/unipixel/functions/consent-i18n.php.
 */
$english = array(
    'title'                 => 'Your Privacy Choices',
    'body'                  => 'This site uses cookies or similar technologies for technical purposes and, with your consent, for functionality, experience, measurement and marketing (personalized ads). You can choose which categories you are happy for us to use before continuing, or by clicking Accept.',
    'btn_accept'            => 'Accept all',
    'btn_adjust'            => 'Adjust preferences',
    'btn_reject'            => 'Reject all',
    'panel_title'           => 'Manage Your Preferences',
    'panel_body'            => 'You can control which types of events are allowed to be sent from this site.',
    'cat_functional_label'  => 'Functional cookies',
    'cat_functional_desc'   => 'used to keep your preferences saved (like this consent choice) and enable essential plugin functionality.',
    'cat_performance_label' => 'Performance cookies',
    'cat_performance_desc'  => 'allow anonymous analytics data for improving how conversion events (like <em>page_view</em> or <em>add_to_cart</em>) are tracked and measured.',
    'cat_marketing_label'   => 'Marketing cookies',
    'cat_marketing_desc'    => 'enable tracking for advertising platforms like Meta, Google Ads, and TikTok, so that conversions can be reported back to those platforms.',
    'panel_footer'          => '<strong>Necessary cookies</strong> are always on and required for the site to function correctly. They do not include any marketing or analytics data.',
    'btn_cancel'            => 'Cancel',
    'btn_save'              => 'Save preferences',
);

/**
 * Translations per locale. Keys match $english. Machine-assisted; admins can override per-field.
 * Non-ASCII characters (diacritics, CJK, Cyrillic, Arabic) are safe in PHP string literals
 * since this file is UTF-8 encoded.
 */
$locales = array();

$locales['es_ES'] = array(
    'title'                 => 'Tus opciones de privacidad',
    'body'                  => 'Este sitio utiliza cookies o tecnologías similares para fines técnicos y, con tu consentimiento, para funcionalidad, experiencia, medición y marketing (anuncios personalizados). Puedes elegir qué categorías quieres permitirnos usar antes de continuar, o haciendo clic en Aceptar.',
    'btn_accept'            => 'Aceptar todo',
    'btn_adjust'            => 'Ajustar preferencias',
    'btn_reject'            => 'Rechazar todo',
    'panel_title'           => 'Gestiona tus preferencias',
    'panel_body'            => 'Puedes controlar qué tipos de eventos se pueden enviar desde este sitio.',
    'cat_functional_label'  => 'Cookies funcionales',
    'cat_functional_desc'   => 'se utilizan para mantener tus preferencias guardadas (como esta elección de consentimiento) y habilitar la funcionalidad esencial del plugin.',
    'cat_performance_label' => 'Cookies de rendimiento',
    'cat_performance_desc'  => 'permiten datos analíticos anónimos para mejorar el seguimiento y la medición de eventos de conversión (como <em>page_view</em> o <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Cookies de marketing',
    'cat_marketing_desc'    => 'habilitan el seguimiento para plataformas publicitarias como Meta, Google Ads y TikTok, para que las conversiones puedan reportarse a esas plataformas.',
    'panel_footer'          => '<strong>Las cookies necesarias</strong> están siempre activas y son necesarias para que el sitio funcione correctamente. No incluyen datos de marketing ni analíticos.',
    'btn_cancel'            => 'Cancelar',
    'btn_save'              => 'Guardar preferencias',
);

$locales['es_MX'] = $locales['es_ES']; // Mexican Spanish — identical short UI strings. Divergence via admin override.

$locales['fr_FR'] = array(
    'title'                 => 'Vos choix en matière de confidentialité',
    'body'                  => 'Ce site utilise des cookies ou des technologies similaires à des fins techniques et, avec votre consentement, à des fins de fonctionnalité, d\'expérience, de mesure et de marketing (publicités personnalisées). Vous pouvez choisir les catégories que vous autorisez avant de continuer, ou en cliquant sur Accepter.',
    'btn_accept'            => 'Tout accepter',
    'btn_adjust'            => 'Ajuster les préférences',
    'btn_reject'            => 'Tout refuser',
    'panel_title'           => 'Gérez vos préférences',
    'panel_body'            => 'Vous pouvez contrôler quels types d\'événements sont autorisés à être envoyés depuis ce site.',
    'cat_functional_label'  => 'Cookies fonctionnels',
    'cat_functional_desc'   => 'utilisés pour conserver vos préférences (comme ce choix de consentement) et activer les fonctionnalités essentielles du plugin.',
    'cat_performance_label' => 'Cookies de performance',
    'cat_performance_desc'  => 'autorisent des données analytiques anonymes pour améliorer le suivi et la mesure des événements de conversion (comme <em>page_view</em> ou <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Cookies marketing',
    'cat_marketing_desc'    => 'activent le suivi pour les plateformes publicitaires comme Meta, Google Ads et TikTok, afin que les conversions puissent être signalées à ces plateformes.',
    'panel_footer'          => '<strong>Les cookies nécessaires</strong> sont toujours activés et requis pour le bon fonctionnement du site. Ils n\'incluent aucune donnée marketing ou analytique.',
    'btn_cancel'            => 'Annuler',
    'btn_save'              => 'Enregistrer les préférences',
);

$locales['de_DE'] = array(
    'title'                 => 'Ihre Datenschutzoptionen',
    'body'                  => 'Diese Website verwendet Cookies oder ähnliche Technologien für technische Zwecke und, mit Ihrer Zustimmung, für Funktionalität, Erlebnis, Messung und Marketing (personalisierte Werbung). Sie können vor dem Fortfahren wählen, welche Kategorien wir verwenden dürfen, oder auf Akzeptieren klicken.',
    'btn_accept'            => 'Alle akzeptieren',
    'btn_adjust'            => 'Einstellungen anpassen',
    'btn_reject'            => 'Alle ablehnen',
    'panel_title'           => 'Einstellungen verwalten',
    'panel_body'            => 'Sie können steuern, welche Arten von Ereignissen von dieser Website gesendet werden dürfen.',
    'cat_functional_label'  => 'Funktionale Cookies',
    'cat_functional_desc'   => 'werden verwendet, um Ihre Einstellungen zu speichern (wie diese Zustimmungswahl) und wesentliche Plugin-Funktionen zu ermöglichen.',
    'cat_performance_label' => 'Performance-Cookies',
    'cat_performance_desc'  => 'erlauben anonyme Analysedaten zur Verbesserung der Verfolgung und Messung von Conversion-Ereignissen (wie <em>page_view</em> oder <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Marketing-Cookies',
    'cat_marketing_desc'    => 'ermöglichen das Tracking für Werbeplattformen wie Meta, Google Ads und TikTok, damit Conversions an diese Plattformen zurückgemeldet werden können.',
    'panel_footer'          => '<strong>Notwendige Cookies</strong> sind immer aktiv und erforderlich, damit die Website korrekt funktioniert. Sie enthalten keine Marketing- oder Analysedaten.',
    'btn_cancel'            => 'Abbrechen',
    'btn_save'              => 'Einstellungen speichern',
);

$locales['it_IT'] = array(
    'title'                 => 'Le tue scelte sulla privacy',
    'body'                  => 'Questo sito utilizza cookie o tecnologie simili per scopi tecnici e, con il tuo consenso, per funzionalità, esperienza, misurazione e marketing (annunci personalizzati). Puoi scegliere quali categorie autorizzare prima di continuare, o facendo clic su Accetta.',
    'btn_accept'            => 'Accetta tutto',
    'btn_adjust'            => 'Regola preferenze',
    'btn_reject'            => 'Rifiuta tutto',
    'panel_title'           => 'Gestisci le tue preferenze',
    'panel_body'            => 'Puoi controllare quali tipi di eventi possono essere inviati da questo sito.',
    'cat_functional_label'  => 'Cookie funzionali',
    'cat_functional_desc'   => 'utilizzati per mantenere le tue preferenze salvate (come questa scelta di consenso) e abilitare le funzionalità essenziali del plugin.',
    'cat_performance_label' => 'Cookie di prestazione',
    'cat_performance_desc'  => 'consentono dati analitici anonimi per migliorare il tracciamento e la misurazione degli eventi di conversione (come <em>page_view</em> o <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Cookie di marketing',
    'cat_marketing_desc'    => 'abilitano il tracciamento per piattaforme pubblicitarie come Meta, Google Ads e TikTok, in modo che le conversioni possano essere riportate a tali piattaforme.',
    'panel_footer'          => '<strong>I cookie necessari</strong> sono sempre attivi e richiesti per il corretto funzionamento del sito. Non includono dati di marketing o analitici.',
    'btn_cancel'            => 'Annulla',
    'btn_save'              => 'Salva preferenze',
);

$locales['pt_BR'] = array(
    'title'                 => 'Suas opções de privacidade',
    'body'                  => 'Este site usa cookies ou tecnologias similares para fins técnicos e, com seu consentimento, para funcionalidade, experiência, medição e marketing (anúncios personalizados). Você pode escolher quais categorias autorizar antes de continuar, ou clicando em Aceitar.',
    'btn_accept'            => 'Aceitar tudo',
    'btn_adjust'            => 'Ajustar preferências',
    'btn_reject'            => 'Rejeitar tudo',
    'panel_title'           => 'Gerenciar suas preferências',
    'panel_body'            => 'Você pode controlar quais tipos de eventos podem ser enviados por este site.',
    'cat_functional_label'  => 'Cookies funcionais',
    'cat_functional_desc'   => 'usados para manter suas preferências salvas (como esta escolha de consentimento) e habilitar a funcionalidade essencial do plugin.',
    'cat_performance_label' => 'Cookies de desempenho',
    'cat_performance_desc'  => 'permitem dados analíticos anônimos para melhorar o rastreamento e a medição de eventos de conversão (como <em>page_view</em> ou <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Cookies de marketing',
    'cat_marketing_desc'    => 'habilitam o rastreamento para plataformas de publicidade como Meta, Google Ads e TikTok, para que as conversões possam ser relatadas a essas plataformas.',
    'panel_footer'          => '<strong>Os cookies necessários</strong> estão sempre ativos e são necessários para o funcionamento correto do site. Não incluem dados de marketing ou analíticos.',
    'btn_cancel'            => 'Cancelar',
    'btn_save'              => 'Salvar preferências',
);

$locales['pt_PT'] = array(
    'title'                 => 'As suas escolhas de privacidade',
    'body'                  => 'Este site utiliza cookies ou tecnologias semelhantes para fins técnicos e, com o seu consentimento, para funcionalidade, experiência, medição e marketing (anúncios personalizados). Pode escolher quais categorias autorizar antes de continuar, ou clicando em Aceitar.',
    'btn_accept'            => 'Aceitar tudo',
    'btn_adjust'            => 'Ajustar preferências',
    'btn_reject'            => 'Rejeitar tudo',
    'panel_title'           => 'Gerir as suas preferências',
    'panel_body'            => 'Pode controlar que tipos de eventos podem ser enviados por este site.',
    'cat_functional_label'  => 'Cookies funcionais',
    'cat_functional_desc'   => 'utilizados para manter as suas preferências guardadas (como esta escolha de consentimento) e permitir a funcionalidade essencial do plugin.',
    'cat_performance_label' => 'Cookies de desempenho',
    'cat_performance_desc'  => 'permitem dados analíticos anónimos para melhorar o acompanhamento e a medição de eventos de conversão (como <em>page_view</em> ou <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Cookies de marketing',
    'cat_marketing_desc'    => 'permitem o acompanhamento para plataformas de publicidade como Meta, Google Ads e TikTok, para que as conversões possam ser reportadas a essas plataformas.',
    'panel_footer'          => '<strong>Os cookies necessários</strong> estão sempre ativos e são necessários para o funcionamento correto do site. Não incluem dados de marketing nem analíticos.',
    'btn_cancel'            => 'Cancelar',
    'btn_save'              => 'Guardar preferências',
);

$locales['nl_NL'] = array(
    'title'                 => 'Uw privacykeuzes',
    'body'                  => 'Deze site gebruikt cookies of vergelijkbare technologieën voor technische doeleinden en, met uw toestemming, voor functionaliteit, ervaring, meting en marketing (gepersonaliseerde advertenties). U kunt kiezen welke categorieën wij mogen gebruiken voordat u doorgaat, of door op Accepteren te klikken.',
    'btn_accept'            => 'Alles accepteren',
    'btn_adjust'            => 'Voorkeuren aanpassen',
    'btn_reject'            => 'Alles weigeren',
    'panel_title'           => 'Beheer uw voorkeuren',
    'panel_body'            => 'U kunt bepalen welke soorten gebeurtenissen vanuit deze site mogen worden verzonden.',
    'cat_functional_label'  => 'Functionele cookies',
    'cat_functional_desc'   => 'gebruikt om uw voorkeuren opgeslagen te houden (zoals deze toestemmingskeuze) en essentiële plugin-functionaliteit mogelijk te maken.',
    'cat_performance_label' => 'Prestatiecookies',
    'cat_performance_desc'  => 'staan anonieme analysegegevens toe voor het verbeteren van het bijhouden en meten van conversiegebeurtenissen (zoals <em>page_view</em> of <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Marketingcookies',
    'cat_marketing_desc'    => 'schakelen tracking in voor advertentieplatforms zoals Meta, Google Ads en TikTok, zodat conversies aan die platforms kunnen worden gerapporteerd.',
    'panel_footer'          => '<strong>Noodzakelijke cookies</strong> staan altijd aan en zijn vereist voor de correcte werking van de site. Ze bevatten geen marketing- of analysegegevens.',
    'btn_cancel'            => 'Annuleren',
    'btn_save'              => 'Voorkeuren opslaan',
);

$locales['pl_PL'] = array(
    'title'                 => 'Twoje wybory dotyczące prywatności',
    'body'                  => 'Ta witryna używa plików cookie lub podobnych technologii w celach technicznych oraz, za Twoją zgodą, w celach funkcjonalnych, doświadczeniowych, pomiarowych i marketingowych (reklamy spersonalizowane). Możesz wybrać, które kategorie zezwalasz nam używać przed kontynuowaniem, lub klikając Akceptuj.',
    'btn_accept'            => 'Zaakceptuj wszystko',
    'btn_adjust'            => 'Dostosuj preferencje',
    'btn_reject'            => 'Odrzuć wszystko',
    'panel_title'           => 'Zarządzaj preferencjami',
    'panel_body'            => 'Możesz kontrolować, jakie typy zdarzeń mogą być wysyłane z tej witryny.',
    'cat_functional_label'  => 'Pliki cookie funkcjonalne',
    'cat_functional_desc'   => 'używane do zachowania Twoich preferencji (jak ten wybór zgody) i włączenia niezbędnych funkcji wtyczki.',
    'cat_performance_label' => 'Pliki cookie wydajnościowe',
    'cat_performance_desc'  => 'pozwalają na anonimowe dane analityczne w celu ulepszenia śledzenia i pomiaru zdarzeń konwersji (takich jak <em>page_view</em> lub <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Pliki cookie marketingowe',
    'cat_marketing_desc'    => 'włączają śledzenie dla platform reklamowych takich jak Meta, Google Ads i TikTok, aby konwersje mogły być raportowane z powrotem do tych platform.',
    'panel_footer'          => '<strong>Pliki cookie niezbędne</strong> są zawsze włączone i wymagane do prawidłowego działania witryny. Nie zawierają żadnych danych marketingowych ani analitycznych.',
    'btn_cancel'            => 'Anuluj',
    'btn_save'              => 'Zapisz preferencje',
);

$locales['ja'] = array(
    'title'                 => 'プライバシーの選択',
    'body'                  => 'このサイトは、技術的な目的のため、およびお客様の同意に基づき、機能、体験、測定、マーケティング（パーソナライズド広告）のためにクッキーまたは類似の技術を使用します。続行する前にどのカテゴリを許可するかを選択するか、「すべて許可」をクリックしてください。',
    'btn_accept'            => 'すべて許可',
    'btn_adjust'            => '設定を調整',
    'btn_reject'            => 'すべて拒否',
    'panel_title'           => '設定を管理',
    'panel_body'            => 'このサイトから送信されるイベントの種類を制御できます。',
    'cat_functional_label'  => '機能クッキー',
    'cat_functional_desc'   => '設定（この同意の選択など）を保存し、プラグインの基本機能を有効にするために使用されます。',
    'cat_performance_label' => 'パフォーマンスクッキー',
    'cat_performance_desc'  => 'コンバージョンイベント（<em>page_view</em>や<em>add_to_cart</em>など）の追跡と測定を改善するために匿名の分析データを許可します。',
    'cat_marketing_label'   => 'マーケティングクッキー',
    'cat_marketing_desc'    => 'Meta、Google Ads、TikTokなどの広告プラットフォームのトラッキングを有効にし、コンバージョンをそれらのプラットフォームに報告できるようにします。',
    'panel_footer'          => '<strong>必要なクッキー</strong>は常に有効で、サイトが正しく機能するために必要です。マーケティングや分析データは含まれません。',
    'btn_cancel'            => 'キャンセル',
    'btn_save'              => '設定を保存',
);

$locales['zh_CN'] = array(
    'title'                 => '您的隐私选择',
    'body'                  => '本网站出于技术目的，并在征得您同意的情况下，使用 Cookie 或类似技术用于功能、体验、测量和营销（个性化广告）。您可以在继续之前选择允许我们使用哪些类别，或点击接受。',
    'btn_accept'            => '全部接受',
    'btn_adjust'            => '调整偏好',
    'btn_reject'            => '全部拒绝',
    'panel_title'           => '管理您的偏好',
    'panel_body'            => '您可以控制允许从此网站发送哪些类型的事件。',
    'cat_functional_label'  => '功能性 Cookie',
    'cat_functional_desc'   => '用于保存您的偏好（如此同意选择）并启用插件的基本功能。',
    'cat_performance_label' => '性能 Cookie',
    'cat_performance_desc'  => '允许匿名分析数据，以改进对转化事件（如 <em>page_view</em> 或 <em>add_to_cart</em>）的跟踪和测量。',
    'cat_marketing_label'   => '营销 Cookie',
    'cat_marketing_desc'    => '为 Meta、Google Ads 和 TikTok 等广告平台启用跟踪，以便将转化数据报告给这些平台。',
    'panel_footer'          => '<strong>必要 Cookie</strong> 始终开启，是网站正常运行所必需的。它们不包含任何营销或分析数据。',
    'btn_cancel'            => '取消',
    'btn_save'              => '保存偏好',
);

$locales['ar'] = array(
    'title'                 => 'خياراتك للخصوصية',
    'body'                  => 'يستخدم هذا الموقع ملفات تعريف الارتباط أو التقنيات المماثلة لأغراض تقنية، وبموافقتك، للوظائف والتجربة والقياس والتسويق (الإعلانات المخصصة). يمكنك اختيار الفئات التي تسمح لنا باستخدامها قبل المتابعة، أو بالنقر على قبول.',
    'btn_accept'            => 'قبول الكل',
    'btn_adjust'            => 'ضبط التفضيلات',
    'btn_reject'            => 'رفض الكل',
    'panel_title'           => 'إدارة تفضيلاتك',
    'panel_body'            => 'يمكنك التحكم في أنواع الأحداث المسموح بإرسالها من هذا الموقع.',
    'cat_functional_label'  => 'ملفات تعريف الارتباط الوظيفية',
    'cat_functional_desc'   => 'تستخدم للحفاظ على تفضيلاتك محفوظة (مثل خيار الموافقة هذا) وتمكين الوظائف الأساسية للإضافة.',
    'cat_performance_label' => 'ملفات تعريف الارتباط الخاصة بالأداء',
    'cat_performance_desc'  => 'تسمح ببيانات التحليلات المجهولة لتحسين كيفية تتبع وقياس أحداث التحويل (مثل <em>page_view</em> أو <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'ملفات تعريف الارتباط التسويقية',
    'cat_marketing_desc'    => 'تتيح التتبع لمنصات الإعلان مثل Meta وGoogle Ads وTikTok، بحيث يمكن الإبلاغ عن التحويلات إلى تلك المنصات.',
    'panel_footer'          => '<strong>ملفات تعريف الارتباط الضرورية</strong> مفعلة دائما ومطلوبة ليعمل الموقع بشكل صحيح. وهي لا تتضمن أي بيانات تسويقية أو تحليلية.',
    'btn_cancel'            => 'إلغاء',
    'btn_save'              => 'حفظ التفضيلات',
);

$locales['ko_KR'] = array(
    'title'                 => '개인정보 선택',
    'body'                  => '이 사이트는 기술적 목적과 귀하의 동의에 따라 기능, 경험, 측정 및 마케팅(맞춤형 광고)을 위해 쿠키 또는 유사한 기술을 사용합니다. 계속하기 전에 허용할 카테고리를 선택하거나 모두 허용을 클릭할 수 있습니다.',
    'btn_accept'            => '모두 허용',
    'btn_adjust'            => '환경설정 조정',
    'btn_reject'            => '모두 거부',
    'panel_title'           => '환경설정 관리',
    'panel_body'            => '이 사이트에서 전송되는 이벤트 유형을 제어할 수 있습니다.',
    'cat_functional_label'  => '기능 쿠키',
    'cat_functional_desc'   => '환경설정(이 동의 선택 등)을 저장하고 플러그인의 필수 기능을 활성화하는 데 사용됩니다.',
    'cat_performance_label' => '성능 쿠키',
    'cat_performance_desc'  => '전환 이벤트(<em>page_view</em> 또는 <em>add_to_cart</em> 등)의 추적 및 측정을 개선하기 위한 익명 분석 데이터를 허용합니다.',
    'cat_marketing_label'   => '마케팅 쿠키',
    'cat_marketing_desc'    => 'Meta, Google Ads, TikTok 등의 광고 플랫폼에 대한 추적을 활성화하여 전환을 해당 플랫폼에 보고할 수 있도록 합니다.',
    'panel_footer'          => '<strong>필수 쿠키</strong>는 항상 활성화되어 있으며 사이트가 올바르게 작동하는 데 필요합니다. 마케팅 또는 분석 데이터는 포함되지 않습니다.',
    'btn_cancel'            => '취소',
    'btn_save'              => '환경설정 저장',
);

$locales['tr_TR'] = array(
    'title'                 => 'Gizlilik Tercihleriniz',
    'body'                  => 'Bu site teknik amaçlarla ve izniniz doğrultusunda işlevsellik, deneyim, ölçüm ve pazarlama (kişiselleştirilmiş reklamlar) için çerezleri veya benzer teknolojileri kullanır. Devam etmeden önce hangi kategorilere izin vereceğinizi seçebilir veya Kabul Et düğmesine tıklayabilirsiniz.',
    'btn_accept'            => 'Tümünü Kabul Et',
    'btn_adjust'            => 'Tercihleri Ayarla',
    'btn_reject'            => 'Tümünü Reddet',
    'panel_title'           => 'Tercihlerinizi Yönetin',
    'panel_body'            => 'Bu siteden hangi tür olayların gönderilmesine izin verileceğini kontrol edebilirsiniz.',
    'cat_functional_label'  => 'İşlevsel çerezler',
    'cat_functional_desc'   => 'tercihlerinizin (bu izin seçimi gibi) kaydedilmesini sağlamak ve temel eklenti işlevlerini etkinleştirmek için kullanılır.',
    'cat_performance_label' => 'Performans çerezleri',
    'cat_performance_desc'  => 'dönüşüm olaylarının (<em>page_view</em> veya <em>add_to_cart</em> gibi) izlenmesini ve ölçülmesini iyileştirmek için anonim analiz verilerine izin verir.',
    'cat_marketing_label'   => 'Pazarlama çerezleri',
    'cat_marketing_desc'    => 'Meta, Google Ads ve TikTok gibi reklam platformları için izlemeyi etkinleştirerek dönüşümlerin bu platformlara bildirilmesini sağlar.',
    'panel_footer'          => '<strong>Gerekli çerezler</strong> her zaman açıktır ve sitenin doğru çalışması için gereklidir. Herhangi bir pazarlama veya analiz verisi içermezler.',
    'btn_cancel'            => 'İptal',
    'btn_save'              => 'Tercihleri Kaydet',
);

$locales['zh_TW'] = array(
    'title'                 => '您的隱私選擇',
    'body'                  => '本網站基於技術目的，並在您同意的情況下，使用 Cookie 或類似技術用於功能、體驗、測量和行銷（個人化廣告）。您可以在繼續之前選擇允許我們使用哪些類別，或點擊接受。',
    'btn_accept'            => '全部接受',
    'btn_adjust'            => '調整偏好設定',
    'btn_reject'            => '全部拒絕',
    'panel_title'           => '管理您的偏好設定',
    'panel_body'            => '您可以控制允許從此網站傳送哪些類型的事件。',
    'cat_functional_label'  => '功能性 Cookie',
    'cat_functional_desc'   => '用於保留您的偏好（如此同意選擇）並啟用外掛的基本功能。',
    'cat_performance_label' => '效能 Cookie',
    'cat_performance_desc'  => '允許匿名分析資料，以改善對轉換事件（如 <em>page_view</em> 或 <em>add_to_cart</em>）的追蹤與測量。',
    'cat_marketing_label'   => '行銷 Cookie',
    'cat_marketing_desc'    => '為 Meta、Google Ads 和 TikTok 等廣告平台啟用追蹤，使轉換能回報給這些平台。',
    'panel_footer'          => '<strong>必要 Cookie</strong> 始終開啟，並且是網站正常運作所必需的。它們不包含任何行銷或分析資料。',
    'btn_cancel'            => '取消',
    'btn_save'              => '儲存偏好設定',
);

$locales['sv_SE'] = array(
    'title'                 => 'Dina sekretessval',
    'body'                  => 'Denna webbplats använder cookies eller liknande teknologier för tekniska ändamål och, med ditt samtycke, för funktionalitet, upplevelse, mätning och marknadsföring (personaliserade annonser). Du kan välja vilka kategorier du tillåter oss att använda innan du fortsätter, eller genom att klicka på Acceptera.',
    'btn_accept'            => 'Acceptera alla',
    'btn_adjust'            => 'Justera inställningar',
    'btn_reject'            => 'Avvisa alla',
    'panel_title'           => 'Hantera dina inställningar',
    'panel_body'            => 'Du kan kontrollera vilka typer av händelser som tillåts skickas från denna webbplats.',
    'cat_functional_label'  => 'Funktionella cookies',
    'cat_functional_desc'   => 'används för att behålla dina inställningar sparade (som detta samtyckesval) och aktivera väsentliga insticksfunktioner.',
    'cat_performance_label' => 'Prestandacookies',
    'cat_performance_desc'  => 'tillåter anonym analysdata för att förbättra hur konverteringshändelser (som <em>page_view</em> eller <em>add_to_cart</em>) spåras och mäts.',
    'cat_marketing_label'   => 'Marknadsföringscookies',
    'cat_marketing_desc'    => 'aktiverar spårning för annonsplattformar som Meta, Google Ads och TikTok, så att konverteringar kan rapporteras tillbaka till dessa plattformar.',
    'panel_footer'          => '<strong>Nödvändiga cookies</strong> är alltid aktiverade och krävs för att webbplatsen ska fungera korrekt. De innehåller inga marknadsförings- eller analysdata.',
    'btn_cancel'            => 'Avbryt',
    'btn_save'              => 'Spara inställningar',
);

$locales['cs_CZ'] = array(
    'title'                 => 'Vaše volby ochrany soukromí',
    'body'                  => 'Tento web používá cookies nebo podobné technologie pro technické účely a s vaším souhlasem pro funkčnost, zážitek, měření a marketing (personalizované reklamy). Před pokračováním si můžete vybrat, které kategorie nám dovolíte používat, nebo kliknout na Přijmout.',
    'btn_accept'            => 'Přijmout vše',
    'btn_adjust'            => 'Upravit předvolby',
    'btn_reject'            => 'Odmítnout vše',
    'panel_title'           => 'Spravovat vaše předvolby',
    'panel_body'            => 'Můžete kontrolovat, jaké typy událostí je dovoleno odesílat z tohoto webu.',
    'cat_functional_label'  => 'Funkční cookies',
    'cat_functional_desc'   => 'používají se k uchování vašich předvoleb (jako je tato volba souhlasu) a k umožnění základních funkcí pluginu.',
    'cat_performance_label' => 'Výkonové cookies',
    'cat_performance_desc'  => 'umožňují anonymní analytická data pro zlepšení sledování a měření konverzních událostí (jako <em>page_view</em> nebo <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Marketingové cookies',
    'cat_marketing_desc'    => 'povolují sledování pro reklamní platformy jako Meta, Google Ads a TikTok, aby konverze mohly být nahlášeny zpět těmto platformám.',
    'panel_footer'          => '<strong>Nezbytné cookies</strong> jsou vždy zapnuté a vyžadované pro správné fungování webu. Neobsahují žádná marketingová ani analytická data.',
    'btn_cancel'            => 'Zrušit',
    'btn_save'              => 'Uložit předvolby',
);

$locales['ru_RU'] = array(
    'title'                 => 'Ваши настройки конфиденциальности',
    'body'                  => 'Этот сайт использует файлы cookie или аналогичные технологии в технических целях и, с вашего согласия, для функциональности, опыта, измерений и маркетинга (персонализированная реклама). Вы можете выбрать, какие категории разрешить, прежде чем продолжить, или нажав Принять.',
    'btn_accept'            => 'Принять все',
    'btn_adjust'            => 'Настроить предпочтения',
    'btn_reject'            => 'Отклонить все',
    'panel_title'           => 'Управление предпочтениями',
    'panel_body'            => 'Вы можете контролировать, какие типы событий разрешены для отправки с этого сайта.',
    'cat_functional_label'  => 'Функциональные cookies',
    'cat_functional_desc'   => 'используются для сохранения ваших предпочтений (например, этого выбора согласия) и обеспечения основных функций плагина.',
    'cat_performance_label' => 'Cookies производительности',
    'cat_performance_desc'  => 'разрешают анонимные аналитические данные для улучшения отслеживания и измерения событий конверсии (таких как <em>page_view</em> или <em>add_to_cart</em>).',
    'cat_marketing_label'   => 'Маркетинговые cookies',
    'cat_marketing_desc'    => 'включают отслеживание для рекламных платформ, таких как Meta, Google Ads и TikTok, чтобы конверсии могли передаваться обратно на эти платформы.',
    'panel_footer'          => '<strong>Необходимые cookies</strong> всегда включены и требуются для правильной работы сайта. Они не включают маркетинговые или аналитические данные.',
    'btn_cancel'            => 'Отмена',
    'btn_save'              => 'Сохранить предпочтения',
);


// === Build .pot file (template — English msgids, empty msgstr) ===
$pot = new PO();
$pot->headers = array(
    'Project-Id-Version'        => 'UniPixel',
    'Report-Msgid-Bugs-To'      => 'https://unipixelhq.com',
    'Content-Type'              => 'text/plain; charset=UTF-8',
    'Content-Transfer-Encoding' => '8bit',
    'MIME-Version'              => '1.0',
    'X-Domain'                  => 'unipixel',
);
foreach ($english as $key => $text) {
    $pot->add_entry(new Translation_Entry(array('singular' => $text)));
}
$pot_path = $lang_dir . '/unipixel.pot';
$pot->export_to_file($pot_path);
echo "Wrote POT: $pot_path\n";


// === Build .mo per locale ===
$written = 0;
foreach ($locales as $locale => $translations) {
    $po = new PO();
    $po->headers = array(
        'Project-Id-Version'        => 'UniPixel',
        'Content-Type'              => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
        'MIME-Version'              => '1.0',
        'Language'                  => $locale,
        'X-Domain'                  => 'unipixel',
    );

    foreach ($english as $key => $source) {
        if (!isset($translations[$key]) || $translations[$key] === '') {
            continue;
        }
        $entry = new Translation_Entry(array(
            'singular'     => $source,
            'translations' => array($translations[$key]),
        ));
        $po->add_entry($entry);
    }

    $mo = new MO();
    $mo->headers = $po->headers;
    $mo->entries = $po->entries;

    $mo_path = $lang_dir . '/unipixel-' . $locale . '.mo';
    $mo->export_to_file($mo_path);
    echo "Wrote MO:  $mo_path\n";
    $written++;
}

echo "\nDone. POT + $written MO files written to $lang_dir\n";
