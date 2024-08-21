<?php
    if (!isset($__inertiaSsrDispatched)) {
        $__inertiaSsrDispatched = true;
        $__inertiaSsrResponse = Core::make(\InertiaConcrete\Ssr\Gateway::class)->dispatch($pageSSR);
    }

    if ($__inertiaSsrResponse) {
        echo $__inertiaSsrResponse->head;
    }
?>