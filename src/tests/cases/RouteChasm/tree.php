<?php

use core\path\Part;
use core\path\PartType;
use core\path\Segment;
use core\tree\Node;
use sptf\Sptf;

function addChild(Node $root, int $order, bool $isAnyTerminated): void {
    $segment = new Segment();
    $segment->addPart(new Part(PartType::STATIC, "$order"));

    if ($isAnyTerminated) {
        $segment->setFlag(Segment::FLAG_ANY_TERMINATED);
    }

    $node = new Node();
    $node->setSegment($segment);

    $root->addChild($node);
}

Sptf::test("Add any terminated children at the end of children array", function () {
    $root = new Node();

    addChild($root, 0, false);
    addChild($root, 4, true);
    addChild($root, 1, false);
    addChild($root, 3, true);
    addChild($root, 2, false);

    $children = $root->getChildren();
    $expect = [false, false, false, true, true];

    for ($i = 0; $i < count($children); $i++) {
        Sptf::expect("". $children[$i]->getSegment())
            ->toBe("$i");
        Sptf::expect($children[$i]->getSegment()->hasFlag(Segment::FLAG_ANY_TERMINATED))
            ->toBe($expect[$i]);
    }
});