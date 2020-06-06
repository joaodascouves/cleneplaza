<?php

    /**
     * @param Array $parameters
     * @return Array
     */
    function push_amount($parameters)
    {
        global $conn;

        if( ($amount = @validate_natural_num($parameters['amount'])) <= 0 )
            return Array(
                'status' => 1,
                'message'=> 'amount must be greater than 0.'
            );

        if( !in_array(($opcode = $parameters['opcode']), ['credit', 'debit']) )
            return Array(
                'status' => 2,
                'message'=> 'OP code must be either credit or debit.'
            );
        
        if( !($user = user_get_by_id(($user_id = $parameters['user_id']))) )
            return Array(
                'status' => 3,
                'message'=> 'Non existent user.'
            );

        if( strlen(($description = $parameters['description']))<5 )
            return Array(
                'status' => 4,
                'message'=> 'Description length must be greater than 5 characters.'
            );

        $subject_id = @validate_natural_num($parameters['subject_id']);
        $subject_context = $parameters['subject_context'];

        $query = mysqli_query($conn, sprintf("INSERT INTO `cl_coins` (`user_id`, `subject_id`, `subject_context`, `opcode`, `amount`, `description`)
            VALUES (%d, %d, '%s', '%s', %f, '%s')",
            
            $user_id,
            $subject_id,
            $subject_context,
            $opcode,
            $amount,
            $description
        ));

        if( !$query || mysqli_affected_rows($conn) === 0 )
            return Array(
                'status' => 5,
                'message'=> mysqli_error($conn)
            );

        return Array(
            'status' => 0,
            'message'=> ''
        );
        
    }

    /**
     * @param Float $amount
     * @param Integer $subject_id
     * @param String $subject_context
     * @param String $description
     * @return Array
     */
    function credit_current_user($amount, $subject_id, $subject_context, $description)
    {
        return push_amount(Array(
            'amount'            => $amount,
            'user_id'           => $_SESSION['user_id'],
            'subject_id'        => $subject_id,
            'subject_context'   => $subject_context,
            'opcode'            => 'credit',
            'description'       => $description
        ));
    }

    /**
     * @param Float $amount
     * @param Integer $subject_id
     * @param String $subject_context
     * @param String $description
     * @return Array
     */
    function debit_current_user($amount, $subject_id, $subject_context, $description)
    {
        return push_amount(Array(
            'amount'            => $amount,
            'user_id'           => $_SESSION['user_id'],
            'subject_id'        => $subject_id,
            'subject_context'   => $subject_context,
            'opcode'            => 'debit',
            'description'       => $description
        ));
    }

    /**
     * @return Array
     */
    function current_user_balance()
    {
        global $conn;

        $query = mysqli_query($conn, sprintf("SELECT `amount`, `opcode` FROM `cl_coins`
            WHERE `user_id`=%d", $_SESSION['user_id']));

        if( !$query || mysqli_num_rows($query) === 0 )
            return Array(
                'status' => 1,
                'message'=> '',
                'amount'=> 0
            );
        
        $total = 0.00;
        while( ($row = mysqli_fetch_assoc($query)) )
        {
            $amount = $row['amount'];

            if( $row['opcode'] === 'debit' )
                $amount *= -1;

            $total += $amount;
        }

        return Array(
            'status' => 1,
            'message'=> '',
            'amount'=> number_format(round($total, 2), 2)
        );
    }