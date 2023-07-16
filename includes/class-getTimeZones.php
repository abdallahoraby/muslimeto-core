<?php


    class getTimeZones{
        public function getTimeZoneValues()
        {
            $result = array();

            unload_textdomain( 'continents-cities' );
            load_textdomain( 'continents-cities', WP_LANG_DIR . '/continents-cities-' . get_locale() . '.mo' );

            $unsorted = array();
            foreach ( timezone_identifiers_list() as $zone_value ) {
                $zone = explode( '/', $zone_value, 2 );
                $key = translate( $zone[0], 'continents-cities' );
                if ( ! array_key_exists( $key, $unsorted ) ) {
                    $unsorted[ $key ] = array();
                }
                $unsorted[ $key ][ $zone_value ] = translate( isset( $zone[1] ) ? implode( ' - ', array_map( function ( $item ) { return translate( $item, 'continents-cities' ); }, explode( '/', str_replace( '_', ' ', $zone[1] ) ) ) ) : $zone[0], 'continents-cities' );
            }

            // Sort arrays
            unset( $unsorted['UTC'] );
            $sorted_continents = array_keys( $unsorted );
            asort( $sorted_continents);
            foreach ( $sorted_continents as $continent ) {
                $continent_data = $unsorted[ $continent ];
                asort($continent_data);
                $result[ $continent ] = $continent_data;
            }
            $result['UTC'] = array( 'UTC' => 'UTC' );

            $offset_range = array(
                - 12,
                - 11.5,
                - 11,
                - 10.5,
                - 10,
                - 9.5,
                - 9,
                - 8.5,
                - 8,
                - 7.5,
                - 7,
                - 6.5,
                - 6,
                - 5.5,
                - 5,
                - 4.5,
                - 4,
                - 3.5,
                - 3,
                - 2.5,
                - 2,
                - 1.5,
                - 1,
                - 0.5,
                0,
                0.5,
                1,
                1.5,
                2,
                2.5,
                3,
                3.5,
                4,
                4.5,
                5,
                5.5,
                5.75,
                6,
                6.5,
                7,
                7.5,
                8,
                8.5,
                8.75,
                9,
                9.5,
                10,
                10.5,
                11,
                11.5,
                12,
                12.75,
                13,
                13.75,
                14,
            );

            foreach ( $offset_range as $offset ) {
                if ( 0 <= $offset ) {
                    $offset_name = '+' . $offset;
                } else {
                    $offset_name = (string) $offset;
                }

                $offset_value = $offset_name;
                $offset_name = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $offset_name );
                $offset_name = 'UTC' . $offset_name;
                $offset_value = 'UTC' . $offset_value;
                $result[ __( 'Manual Offsets' ) ][ $offset_value ] = $offset_name;
            }

            return $result;


        }

        public function getTimeZoneOffset( $timeZoneName ) {
            $tz=timezone_open($timeZoneName);
            $dateTimeOslo=date_create("now",timezone_open("Europe/Oslo"));
            return timezone_offset_get($tz,$dateTimeOslo) / 3600;
        }

    }