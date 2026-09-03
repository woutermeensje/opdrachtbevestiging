{{--
    Ingesloten lettertype voor de PDF's: Poppins (SIL OFL 1.1).
    De .ttf-bestanden staan in storage/fonts/ en worden door dompdf
    ingelezen via onderstaande @font-face-regels.
--}}
<style>
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 300;
        src: url('{{ storage_path('fonts/Poppins-Light.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 400;
        src: url('{{ storage_path('fonts/Poppins-Regular.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 500;
        src: url('{{ storage_path('fonts/Poppins-Medium.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 600;
        src: url('{{ storage_path('fonts/Poppins-SemiBold.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 700;
        src: url('{{ storage_path('fonts/Poppins-Bold.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Poppins';
        font-style: italic;
        font-weight: 400;
        src: url('{{ storage_path('fonts/Poppins-Italic.ttf') }}') format('truetype');
    }
</style>
