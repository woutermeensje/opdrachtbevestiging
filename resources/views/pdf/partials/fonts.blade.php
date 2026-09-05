{{--
    Ingesloten lettertype voor de PDF's: Aptos (© Microsoft Corporation).
    De officiële .ttf-bestanden staan in public/fonts/aptos/ en worden door
    dompdf ingelezen via onderstaande @font-face-regels. dompdf heeft geen
    internettoegang (enable_remote = false), dus de fonts moeten lokaal
    aanwezig zijn. dompdf cachet de metrics in storage/fonts/.
--}}
<style>
    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 300;
        src: url('{{ public_path('fonts/aptos/Aptos-Light.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 400;
        src: url('{{ public_path('fonts/aptos/Aptos.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 500;
        src: url('{{ public_path('fonts/aptos/Aptos.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 600;
        src: url('{{ public_path('fonts/aptos/Aptos-SemiBold.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 700;
        src: url('{{ public_path('fonts/aptos/Aptos-Bold.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: italic;
        font-weight: 400;
        src: url('{{ public_path('fonts/aptos/Aptos-Italic.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: italic;
        font-weight: 700;
        src: url('{{ public_path('fonts/aptos/Aptos-Bold-Italic.ttf') }}') format('truetype');
    }
</style>
