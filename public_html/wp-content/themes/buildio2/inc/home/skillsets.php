<?php
$stats_cards = [
	['value' => '300+', 'label' => 'Website Design',        'icon_class' => 'arr031Svg'],
	['value' => '270+', 'label' => 'Custom Application Development',     'icon_class' => 'arr031Svg'],
	['value' => '450+', 'label' => 'Marketing &amp; Social Media',    'icon_class' => 'arr031Svg'],
	['value' => '120+', 'label' => 'CRM Development and Customisation',           'icon_class' => 'arr031Svg'],
	['value' => '90+',  'label' => 'Phone Systems & SMS',          'icon_class' => 'arr031Svg'],
	['value' => '35+',  'label' => 'Automation &amp; Systems Design',             'icon_class' => 'arr031Svg'],
	['value' => '50+',  'label' => 'AI and Integrations',              'icon_class' => 'arr031Svg'],
	['value' => '25+',  'label' => 'SEO, Ads & Search',            'icon_class' => 'arr031Svg'],
];
?>

<div class="mx-auto mb-6 stats-marquee">
  <div class="stats-marquee__scroller">
    <div class="stats-marquee__inner">

      <div class="stats-marquee__track">
        <?php foreach ($stats_cards as $card) : ?>
          <div class="stats-marquee__item">
            <div class="card card-dashed shadow-none text-center rounded-2 h-100 bg-light">
              <div class="card-body">
                <span class="svg-icon text-primary <?php echo esc_attr($card['icon_class']); ?>"></span>
                <p class="card-text mb-0"><?php echo esc_html($card['label']); ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="stats-marquee__track" aria-hidden="true">
        <?php foreach ($stats_cards as $card) : ?>
          <div class="stats-marquee__item">
            <div class="card card-dashed shadow-none text-center rounded-2 h-100 bg-light">
              <div class="card-body">
                <span class="svg-icon text-primary <?php echo esc_attr($card['icon_class']); ?>"></span>
                <p class="card-text mb-0"><?php echo esc_html($card['label']); ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</div>
