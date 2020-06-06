<?php

    function calculate_credit($config)
    {
        list(
            $min,
            $max,
            $bonus_max,
            $bonus_chance

        ) = array_values($config);

        $base = $min;
        $base += fmod(rand()/.99, $max - $min);

        if( !(rand() % $bonus_chance) )
        {
            $base += fmod(rand()/.99, $bonus_max);
        }

        $base = round($base, 2);
        return $base;
    }