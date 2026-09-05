{{--
    Ingesloten lettertype voor de PDF's: Aptos (© Microsoft Corporation).
    De .ttf-bestanden staan in storage/fonts/ en worden door dompdf
    ingelezen via onderstaande @font-face-regels. dompdf heeft geen
    internettoegang (enable_remote = false), dus de fonts moeten lokaal
    aanwezig zijn.
--}}
<style>
    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 300;
        src: url('{{ storage_path('fonts/Aptos-Regular.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 400;
        src: url('{{ storage_path('fonts/Aptos-Regular.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 500;
        src: url('{{ storage_path('fonts/Aptos-Regular.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 600;
        src: url('{{ storage_path('fonts/Aptos-Bold.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: normal;
        font-weight: 700;
        src: url('{{ storage_path('fonts/Aptos-Bold.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: italic;
        font-weight: 400;
        src: url('{{ storage_path('fonts/Aptos-Italic.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Aptos';
        font-style: italic;
        font-weight: 700;
        src: url('{{ storage_path('fonts/Aptos-BoldItalic.ttf') }}') format('truetype');
    }
</style>
