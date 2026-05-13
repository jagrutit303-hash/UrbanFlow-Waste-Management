<?php
$tips = [
    [
        "icon" => "♻️",
        "title" => "Plastic Ninja",
        "text" => "Rinse your plastic containers before recycling. Clean plastic has a 50% higher chance of being reused!",
        "color" => "#3b82f6"
    ],
    [
        "icon" => "🍎",
        "title" => "Compost King",
        "text" => "Organic waste makes up 40% of our trash. Start composting today and turn your scraps into garden gold!",
        "color" => "#10b981"
    ],
    [
        "icon" => "🔋",
        "title" => "Battery Boss",
        "text" => "Never throw batteries in the trash. They contain chemicals that can leak into our soil. Use designated drop-off points!",
        "color" => "#f59e0b"
    ],
    [
        "icon" => "🛍️",
        "title" => "Bag Hero",
        "text" => "Switch to a reusable cloth bag. One person using reusable bags saves over 22,000 plastic bags in a lifetime!",
        "color" => "#8b5cf6"
    ],
    [
        "icon" => "👕",
        "title" => "Fabric Master",
        "text" => "Donate old clothes instead of dumping them. Textiles can take up to 200 years to decompose in landfills.",
        "color" => "#ec4899"
    ]
];

$random_tip = $tips[array_rand($tips)];
?>

<div class="glass-panel" style="background: rgba(255,255,255,0.9); border-left: 6px solid <?php echo $random_tip['color']; ?>; margin-bottom: 25px; padding: 25px; border-radius: 20px;">
    <div style="display: flex; gap: 20px; align-items: center;">
        <div style="font-size: 3rem; background: <?php echo $random_tip['color']; ?>22; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 20px;">
            <?php echo $random_tip['icon']; ?>
        </div>
        <div>
            <h3 style="margin: 0; color: <?php echo $random_tip['color']; ?>; font-weight: 800; font-size: 1.4rem;">
                <?php echo $random_tip['title']; ?> <span style="font-size: 0.8rem; background: <?php echo $random_tip['color']; ?>; color: white; padding: 3px 8px; border-radius: 10px; margin-left: 10px;">DAILY CHALLENGE</span>
            </h3>
            <p style="margin: 8px 0 0; color: #64748b; font-size: 0.95rem; line-height: 1.5;">
                <?php echo $random_tip['text']; ?>
            </p>
        </div>
    </div>
</div>
