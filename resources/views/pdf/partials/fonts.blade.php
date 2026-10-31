{{--
    Ingesloten lettertype voor de PDF's: Work Sans (SIL Open Font License,
    zie public/fonts/work-sans/OFL.txt). De statische .ttf-bestanden staan in
    public/fonts/work-sans/ en worden door dompdf ingelezen via onderstaande
    @font-face-regels. dompdf heeft geen internettoegang (enable_remote = false),
    dus de fonts moeten lokaal aanwezig zijn. dompdf cachet de metrics in storage/fonts/.
--}}
<style>
    @font-face {
        font-family: 'Work Sans';
        font-style: normal;
        font-weight: 300;
        src: url('{{ public_path('fonts/work-sans/WorkSans-Light.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Work Sans';
        font-style: normal;
        font-weight: 400;
        src: url('{{ public_path('fonts/work-sans/WorkSans-Regular.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Work Sans';
        font-style: normal;
        font-weight: 500;
        src: url('{{ public_path('fonts/work-sans/WorkSans-Medium.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Work Sans';
        font-style: normal;
        font-weight: 600;
        src: url('{{ public_path('fonts/work-sans/WorkSans-SemiBold.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Work Sans';
        font-style: normal;
        font-weight: 700;
        src: url('{{ public_path('fonts/work-sans/WorkSans-Bold.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Work Sans';
        font-style: italic;
        font-weight: 400;
        src: url('{{ public_path('fonts/work-sans/WorkSans-Italic.ttf') }}') format('truetype');
    }

    @font-face {
        font-family: 'Work Sans';
        font-style: italic;
        font-weight: 700;
        src: url('{{ public_path('fonts/work-sans/WorkSans-BoldItalic.ttf') }}') format('truetype');
    }
</style>
