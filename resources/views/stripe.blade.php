<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">Stripe API</div>
                <hr>
                {{$bus}}
                <form action="" method="POST">
                    <script
                            src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                            data-key="pk_test_XTTC79fpSbbhfb6xQDX6bV4G"
                            data-amount="499"
                            data-name="Vinehop Charge"
                            data-description="Pay quote from <?php echo json_encode($bus) ?> ($4.99)"
                            data-image="/128x128.png"
                            data-locale="auto">
                    </script>
                </form>
            </div>
        </div>
    </body>
</html>
