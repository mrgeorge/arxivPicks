CREATE TABLE articles
(
    id integer primary key,
    date text,
    count integer,
    arxiv_id text,
    uri_abs text,
    uri_pdf text,
    title text,
    authors text,
    abstract text
);
